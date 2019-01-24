<?php

// no direct access
defined('EMONCMS_EXEC') or die('Restricted access');

function graph_controller()
{
    global $session,$route;
    $result = "#UNDEFINED#";
    
    if ($route->action=="embed") {
        global $embed; $embed = true;
        $result = view("Modules/graph/embed.php",array());
    } else if ( $route->action=="" || is_numeric($route->action) ) {
        $route->format = "theme";
        $result = view("Modules/graph/view.php", array("session" => $session["read"]));
    }

    return array('content' => $result, 'fullwidth' => true);
}
