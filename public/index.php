<?php
define('DEBUG', true);

if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 'on');
}

include "lib/core.php";

$settings_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR;
include $settings_dir . "settings.php";
// use local dev version if available
if (file_exists( $settings_dir . "settings.dev.php")) {
    include $settings_dir . "settings.dev.php";
}
$settings = isset($settings) ? $settings : array();

$mysqli = new mysqli(
    $mysql_settings["host"],
    $mysql_settings["user"],
    $mysql_settings["password"],
    $mysql_settings["database"],
    $mysql_settings["port"]
);

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

switch ($q)
{
    // example of wrapping a page in a theme view:
    case "":
    case "feeds":
        $format = "themedhtml";
        if ($session["valid"]) {
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
            // pass a full url where path passed as $_POST['next']
            if (!in_array($next, $blacklist)) $content->next = getFullUrl($next);

            // TODO: check that user exists in MQTT server database here...

            if (isset($content->success) && $content->success) {
            
                if (!$stmt = $mysqli->prepare("SELECT username FROM users WHERE username=?")) {
                    $content = array('success'=>false, 'message'=>"Database error");
                }
                $stmt->bind_param("s",$username);
                $stmt->execute();
                $stmt->bind_result($userData_username);
                $result = $stmt->fetch();
                $stmt->close();
                
                
                $valid = false;
                if (!$result) {
                    // ---------------------------------------------------------------------------------
                    // Register user on mqtt server
                    // ---------------------------------------------------------------------------------
                    include "lib/mqtt_hash.php";
                    $mqtthash = create_hash($password);
                    
                    $stmt = $mysqli->prepare("INSERT INTO users ( username, pw, super) VALUES (?,?,0)");
                    $stmt->bind_param("ss", $username, $mqtthash);
                    $result = $stmt->execute();
                    $stmt->close();
                    
                    if ($result) {
                        $topic = "user/$username/#";
                        $stmt = $mysqli->prepare("INSERT INTO acls (username,topic,rw) VALUES (?,?,2)");
                        $stmt->bind_param("ss", $username, $topic);
                        $result = $stmt->execute();
                        $stmt->close();
                        if ($result) $valid = true;
                    }
                } else $valid = true;
                
                if ($valid) {
                    session_regenerate_id();
                    $_SESSION['username'] = $username;
                    $_SESSION['password'] = $password;
                }
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

switch ($format) 
{
    case "themedhtml":
        header('Content-Type: text/html');
        print view("views/theme.php", array('session'=>$session, "content"=>$content));
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
