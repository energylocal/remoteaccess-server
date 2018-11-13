<?php

$username = "emrys";
$password = "emrys";

error_reporting(E_ALL);
ini_set('display_errors', 'on');

include "lib/core.php";

$q = ""; if (isset($_GET['q'])) $q = $_GET['q'];

$format = "html";
$content = "";

switch ($q)
{
    // example of wrapping a page in a theme view:
    case "":
        $format = "themedhtml";
        $content = view("views/feeds.php",array("username"=>$username,"password"=>$password));
        break;

    // json api route
    case "auth":
        $format = "json";
        $result = http_request("POST","https://emoncms.org/user/auth.json",array("username"=>post("username"), "password"=>post("password")));
        $content = json_decode($result);
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
