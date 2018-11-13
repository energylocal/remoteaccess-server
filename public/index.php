<?php

error_reporting(E_ALL);
ini_set('display_errors', 'on');
$path = "https://mqtt.emoncms.org/";

include "lib/core.php";

// Basic session
session_start();
$session = array("valid"=>false, "username"=>false,"password"=>false);
if (isset($_SESSION['username']) && isset($_SESSION['password'])) $session["valid"] = true;
if (isset($_SESSION['username'])) $session["username"] = $_SESSION['username'];
if (isset($_SESSION['password'])) $session["password"] = $_SESSION['password'];

// Route passed via mod rewrite
$q = ""; if (isset($_GET['q'])) $q = $_GET['q'];

$format = "html";
$content = "";

switch ($q)
{
    // example of wrapping a page in a theme view:
    case "":
        $format = "themedhtml";
        if ($session["valid"]) {
            $content = view("views/feeds.php",$session);
        } else {
            $content = view("views/login_view.php",array());
        }
        break;

    case "minimal":
        $format = "themedhtml";
        if ($session["valid"]) {
            $content = view("views/minimal.php",array("session"=>$session));
        } else {
            $content = view("views/login_view.php",array());
        }
        break;

    case "vuetest":
        $format = "themedhtml";
        if ($session["valid"]) {
            $content = view("views/vuetest.php",array("session"=>$session));
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

            $content = json_decode(http_request("POST","https://emoncms.org/user/auth.json",array(
                "username"=>$username,
                "password"=>$password
            )));

            // TODO: check that user exists in MQTT server database here...

            if (isset($content->success) && $content->success) {
                session_regenerate_id();
                $_SESSION['username'] = $username;
                $_SESSION['password'] = $password;
            }
        }
        break;

    case "logout":
        $format = "themedhtml";
        session_unset();
        session_destroy();
        $content = "Logout successful";
        break;
}

switch ($format) 
{
    case "themedhtml":
        header('Content-Type: text/html');
        print view("views/theme.php",array("content"=>$content));
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
