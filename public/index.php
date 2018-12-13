<?php


include "lib/core.php";

$settings_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR;
// use local dev version if available
$dev_settings_filename = 'settings.dev.php';
$settings_filename = 'settings.php';

if (file_exists( $settings_dir . $dev_settings_filename)) {
    include $settings_dir . $dev_settings_filename;
} elseif(file_exists( $settings_dir . $settings_filename)) {
    include $settings_dir . $settings_filename;
}
$settings = isset($settings) ? $settings : array();

if (defined('DEBUG')){
    if(DEBUG === true) {
        error_reporting(E_ALL);
        ini_set('display_errors', 'on');
    }
} else {
    define('DEBUG', false);
}

// Basic session
session_start();
$session = array("valid"=>false, "username"=>false,"password"=>false);
if (isset($_SESSION['username']) && isset($_SESSION['password'])) $session["valid"] = true;
if (isset($_SESSION['username'])) $session["username"] = $_SESSION['username'];
if (isset($_SESSION['password'])) $session["password"] = $_SESSION['password'];

// Route passed via mod rewrite
$q = ""; if (isset($_GET['q'])) $q = $_GET['q'];
// unallowed routes
$blacklist = explode(',', 'login,logout,another-disallowed-path');

$format = "html";
$content = "";

$scripts[] = 'js/jquery-1.11.3.min.js';
$scripts[] = 'js/bootstrap.bundle.min.js';
$ie_scripts = array();
$stylesheets[] = 'css/bootstrap.4.1.3.min.css';

switch ($q)
{
    // example of wrapping a page in a theme view:
    case "":
    case "feeds":
        $format = "themedhtml";
        if ($session["valid"]) {
            // add required js to the theme.php template
            $scripts[] = 'js/misc.js';
            $scripts[] = 'js/vue.js';
            $scripts[] = 'js/mqtt.min.js';
            $scripts[] = 'lib/flot/jquery.flot.merged.js';
            $scripts[] = 'js/vis.helper.js';
            $scripts[] = 'js/feeds.js';
            $scripts[] = 'lib/flot/jquery.flot.resize.js';
            $ie_scripts[] = 'lib/flot/excanvas.min.js';
            $stylesheets[] = 'css/feeds.css';
            $content = view("views/feeds.php", array("settings"=>$settings,"session"=>$session));
        } else {
            $content = view("views/login_view.php");
        }
        break;

    case "minimal":
        $format = "themedhtml";
        if ($session["valid"]) {
            $content = view("views/minimal.php", array("settings"=>$settings,"session"=>$session));
        } else {
            $content = view("views/login_view.php");
        }
        break;

    case "vuetest":
        $format = "themedhtml";
        if ($session["valid"]) {
            $content = view("views/vuetest.php", array("settings"=>$settings,"session"=>$session));
        } else {
            $content = view("views/login_view.php");
        }
        break;

    case "graph":
        $format = "themedhtml";
        if ($session["valid"]) {
            $content = view("views/graph.php", array("settings"=>$settings,"session"=>$session));
        } else {
            $content = view("views/login_view.php");
        }
        break;

    case "graph":
        $format = "themedhtml";
        if ($session["valid"]) {
            $content = view("views/graph.php",array("session"=>$session));
        } else {
            $content = view("views/login_view.php",array());
        }
        break;
        
    // json api route
    case "auth":
        $format = "json";
        if (isset($_POST["username"]) && isset($_POST["password"])) {

            $username = $_POST["username"];
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
                if (!DEBUG) {
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
                        $content = array('success'=>false, 'message'=>"Database connection error", 'code'=>500);
                        break;
                    }

                    if (!isset($mysqli) || !$stmt = $mysqli->prepare("SELECT username FROM users WHERE username=?")) {
                        // the structure of the database doesn't match the prepared statement
                        $content = array('success'=>false, 'message'=>"Precondition Failed", 'code'=>412);

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
                            $stmt = $mysqli->prepare("INSERT INTO users ( username, pw, super) VALUES (?,?,0)");
                            $stmt->bind_param("ss", $username, $mqtthash);
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
                }

                // SAVE THE AUTHORIZED USER'S DETAILS TO THE SESSION
                // --------------------------------------------------------
                if (DEBUG || $db_user_valid) {
                    session_regenerate_id();
                    $_SESSION['username'] = $username;
                    $_SESSION['password'] = $password;
                }
                
            } elseif(isset($content->success) && $content->success === false){
                // Authentication failure in with emoncms.org
                $content = array('success'=>false, 'message'=>"Authentication error", 'code'=>403);
            } else {
                // emoncms.org returned un expected result
                $content = array('success'=>false, 'message'=>"Server Error", 'code'=>500);
            }
        }
	// header("Access-Control-Allow-Origin: *");
        break;

    case "logout":
        $format = "themedhtml";
        session_unset();
        session_destroy();
        $content = '<h2 class="mt-5">Logout successful</h2>';
        break;

    case "login":
        $format = "themedhtml";
        $content = view("views/login_view.php");
        break;

    default:
        $format = "themedhtml";
        $content = "<h4>Error 404</h4>Not Found";
}

// ADD ERROR CODE IF AVAILABLE
if(isset($content) && is_array($content) && !empty($content["code"])){
    http_response_code($content["code"]);
}

// OUTPUT TO BROWSER
switch ($format) 
{
    case "themedhtml":
        header('Content-Type: text/html');
        print view("views/theme.php", array('session'=>$session, "content"=>$content, "scripts"=>$scripts, "ie_scripts"=>$ie_scripts,"stylesheets"=>$stylesheets));
        break;
    case "html":
        header('Content-Type: text/html');
        print $content;
        break;
    case "text":
        header('Content-Type: text/plain');
        print $content;
        break;
    case "json":
        header('Content-Type: application/json');
        print json_encode($content);
        break;
}
