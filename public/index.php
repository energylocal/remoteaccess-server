<?php

define('EMONCMS_EXEC', 1);
error_reporting(E_ALL);
ini_set('display_errors', 'on');

include "core.php";
include "route.php";
include "RemoteAccess.php";

$path = get_application_path();

$settings_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR;

// Load settings
$settings_filename = 'settings.php';
if(file_exists( $settings_dir.$settings_filename)) {
    include $settings_dir.$settings_filename;
}
$settings = isset($settings) ? $settings : array();

// Basic session
session_start();
$session = array("valid"=>0,"username"=>false,"password"=>false,"read"=>0,"write"=>0);
if (isset($_SESSION['username']) && isset($_SESSION['password'])) { $session["valid"] = 1; $session["read"] = 1; } 
if (isset($_SESSION['username'])) $session["username"] = $_SESSION['username'];
if (isset($_SESSION['password'])) $session["password"] = $_SESSION['password'];

//if (isset($_GET["apikey"])) {
//    $apikey = $_GET["apikey"];
//    if ($apikey=="") {
//        $session = array("valid"=>true,"username"=>"","password"=>"","read"=>1,"write"=>0);
//    }
//}

// Route passed via mod rewrite
$q = ""; if (isset($_GET['q'])) $q = $_GET['q'];

// 5) Get route and load controller
$route = new Route(get('q'), server('DOCUMENT_ROOT'), server('REQUEST_METHOD'));

if (get('embed')==1) $embed = 1; else $embed = 0;

// If no route specified use defaults
if ($route->isRouteNotDefined())
{   
    if (!isset($session['read']) || (isset($session['read']) && !$session['read'])) {
        // Non authenticated defaults
        $route->controller = $default_controller;
        $route->action = $default_action;
        $route->subaction = "";
    } else {
        // Authenticated defaults
        $route->controller = $default_controller_auth;
        $route->action = $default_action_auth;
        $route->subaction = "";
    }
}

$output = controller($route->controller);

// If not authenticated and no ouput, asks for login
if ($output['content'] == "#UNDEFINED#" && (!isset($session['read']) || (isset($session['read']) && !$session['read']))) {
    $route->controller = "user";
    $route->action = "login";
    $route->subaction = "";
    $output = controller($route->controller);
}

// --------------------------------------------------------------------------------------------------------------------------
// HTTP to MQTT bridge
// --------------------------------------------------------------------------------------------------------------------------
if ($output["content"] === "#UNDEFINED#" && $session["valid"]) {
    $whitelist = array("feed/list","feed/data","feed/value","feed/timevalue","feed/listwithmeta","feed/fetch","app/list","device/list","demandshaper/get","demandshaper/submit","input/get");
    if (in_array($route->controller."/".$route->action,$whitelist)) {
        $route->format = "json";
        $remoteaccess = new RemoteAccess($session["username"],$session["password"]);
        $output["content"] = $remoteaccess->request($route->controller,$route->action,$route->subaction,$_GET);
    }
}

// If no controller found or nothing is returned, give friendly error
if ($output['content'] === "#UNDEFINED#") {
    header($_SERVER["SERVER_PROTOCOL"]." 406 Not Acceptable");
    $output['content'] = "URI not acceptable. No controller '" . $route->controller . "'. (" . $route->action . "/" . $route->subaction .")";
}

// --------------------------------------------------------------------------------------------------------------------------

// ADD ERROR CODE IF AVAILABLE
// if(isset($content) && is_array($content) && !empty($content["code"])){
//    http_response_code($content["code"]);
// }

// OUTPUT TO BROWSER
switch ($route->format) 
{
    case "themedhtml":
        header('Content-Type: text/html');
        
        $scripts[] = 'js/bootstrap.bundle.min.js';
        $ie_scripts = array(); // scripts to only load in IE
        $stylesheets[] = 'css/bootstrap.4.1.3.min.css';

        $output["session"] = $session;
        $output["scripts"] = $scripts;
        $output["ie_scripts"] = $ie_scripts;
        $output["stylesheets"] = $stylesheets;
        
        print view("Theme/concept/theme.php", $output);
        break;
    case "theme":
        $theme = "basic";
        $output['route'] = $route;
        
        header('Content-Type: text/html');
        if ($embed == 1) {
            print view("Theme/basic/embed.php", $output);
        } else {
            $menu = load_menu();
            $output['mainmenu'] = view("Theme/basic/menu_view.php", array());
            print view("Theme/basic/theme.php", $output);
        }
        break;
    case "html":
        header('Content-Type: text/html');
        print $output["content"];
        break;
    case "text":
        header('Content-Type: text/plain');
        print $output["content"];
        break;
    case "json":
        header('Content-Type: application/json');
        print json_encode($output["content"]);
        break;
}
