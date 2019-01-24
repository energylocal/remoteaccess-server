<?php

// no direct access
defined('EMONCMS_EXEC') or die('Restricted access');

function user_controller()
{
    global $path,$session,$route,$mysql_settings,$blacklist;
    
    if ($route->action=="login") {
        $route->format = "theme";
        return view("Modules/user/login_block.php",array());
    }
    
    elseif ($route->action=="auth") {
        $route->format = "json";
        if (isset($_POST["username"]) && isset($_POST["password"])) {

            $username = trim($_POST["username"]);
            $password = $_POST["password"];
            $next = filter_input(INPUT_POST, "next", FILTER_SANITIZE_STRING);

            $content = json_decode(http_request("POST", "https://emoncms.org/user/auth.json", array(
                "username" => $username,
                "password" => $password,
            )));
            // add "next" prop to $content object. Pass a full url where path passed as $_POST['next']
            if (!in_array($next, $blacklist)) $content->next = getFullUrl($next);

            // Authenticated sucessfully with emoncms.org
            // ----------------------------------------------------------------
            if (isset($content->success) && $content->success === true) {

                // SYNC THE ACL WITH THE AUTHORIZED USER'S DETAILS
                // ------------------------------------------------------------
                try {
                    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
                    // database connection only required to update acl on login
                    $mysqli = new mysqli(
                        $mysql_settings["host"],
                        $mysql_settings["user"],
                        $mysql_settings["password"],
                        $mysql_settings["database"],
                        $mysql_settings["port"]
                    );
                } catch (Exception $e) {
                    // unable to use the desired connection to the mqtt broker acl database
                    return array('success'=>false, 'message'=>"Database connection error", 'code'=>500);
                }

                if (!isset($mysqli) || !$stmt = $mysqli->prepare("SELECT username FROM users WHERE username=?")) {
                    // the structure of the database doesn't match the prepared statement
                    return array('success'=>false, 'message'=>"Precondition Failed", 'code'=>412);

                } else {
                // if mysql connection available find the user
                    $stmt->bind_param("s",$username);
                    $stmt->execute();
                    $stmt->bind_result($userData_username);
                    $result = $stmt->fetch();
                    $stmt->close();
                    
                    $db_user_valid = false;

                    // if no user found add them to the acl
                    if (!$result) {
                        // ---------------------------------------------------------------------------------
                        // Register user on mqtt server
                        // ---------------------------------------------------------------------------------
                        include "lib/mqtt_hash.php";
                        $mqtthash = create_hash($password);
                        
                        // insert new user into users table
                        $stmt = $mysqli->prepare("INSERT INTO users ( username, pw, super, apikey_write, apikey_read) VALUES (?,?,0,?,?)");
                        $stmt->bind_param("ssss", $username,$mqtthash,$content->apikey_write,$content->apikey_read);
                        $result = $stmt->execute();
                        $stmt->close();
                        
                        if ($result) {
                            // if new user successful add the user to the access control list.
                            // access to only the topic (or sub topics) with their username is granted
                            $topic = "user/$username/#";
                            $stmt = $mysqli->prepare("INSERT INTO acls (username, topic, rw) VALUES (?,?,2)");
                            $stmt->bind_param("ss", $username, $topic);
                            $result = $stmt->execute();
                            $stmt->close();
                            if ($result) $db_user_valid = true;
                        }
                    } else {
                        // $result was successful (user found)
                        $db_user_valid = true;
                    }
                }

                // SAVE THE AUTHORIZED USER'S DETAILS TO THE SESSION
                // --------------------------------------------------------
                if ($db_user_valid) {
                    session_regenerate_id();
                    $_SESSION['username'] = $username;
                    $_SESSION['password'] = $password;
                }
                
                // Return success
                return $content;
                
            } elseif(isset($content->success) && $content->success === false){
                // Authentication failure in with emoncms.org
                return array('success'=>false, 'message'=>"Authentication error", 'code'=>403);
            } else {
                // emoncms.org returned un expected result
                return array('success'=>false, 'message'=>"Server Error", 'code'=>500);
            }
        }
    }
    
    elseif ($route->action=="logout") {
        $route->format = "themed";
        session_unset();
        session_destroy();
        header('Location: '.$path);
        die;
    }
    
    elseif ($route->action=="wanip") {
        $route->format = "text";
        return $_SERVER['REMOTE_ADDR'];
    }

    return array('content'=>"#UNDEFINED#");
}
