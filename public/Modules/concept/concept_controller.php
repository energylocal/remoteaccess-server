<?php

// no direct access
defined('EMONCMS_EXEC') or die('Restricted access');

function concept_controller()
{
    global $session,$route,$scripts,$ie_scripts,$stylesheets,$settings;
    
    $result = "#UNDEFINED#";
  
    if ($session["read"]) 
    {
        switch ($route->action) 
        {
            case "":
            case "feeds":
                $route->format = "themedhtml";
                // add required js to the theme.php template
                $scripts[] = 'js/misc.js';
                $scripts[] = 'js/vue.js';
                $scripts[] = 'js/mqtt.min.js';
                $scripts[] = 'lib/flot/jquery.flot.merged.js';
                $scripts[] = 'js/feeds.js';
                $scripts[] = 'lib/flot/jquery.flot.resize.js';
                $ie_scripts[] = 'lib/flot/excanvas.min.js';
                $stylesheets[] = 'css/feeds.css';
                $result = view("Modules/concept/feeds.php", array("settings"=>$settings,"session"=>$session));
                break;

            case "minimal":
                $route->format = "themedhtml";
                $scripts[] = 'js/misc.js';
                $scripts[] = 'js/mqtt.min.js';
                $scripts[] = 'js/vis.helper.js';
                $scripts[] = 'js/minimal.js';
                $result = view("Modules/concept/minimal.php", array("settings"=>$settings,"session"=>$session));
                break;

            case "vuetest":
                $route->format = "themedhtml";
                $scripts[] = 'js/misc.js';
                $scripts[] = 'js/vue.js';
                $scripts[] = 'js/mqtt.min.js';
                $result = view("Modules/concept/vuetest.php", array("settings"=>$settings,"session"=>$session));
                break;

            case "graph":
                $route->format = "themedhtml";
                $scripts[] = 'js/misc.js';
                $scripts[] = 'js/graph.js';
                $result = view("Modules/concept/graph.php", array("settings"=>$settings,"session"=>$session));
                break;
                
            default:
                // default
        }
    }

    return array('content' => $result, 'fullwidth' => true);
}
