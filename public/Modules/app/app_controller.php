<?php
/*
 All Emoncms code is released under the GNU Affero General Public License.
 See COPYRIGHT.txt and LICENSE.txt.
 
 ---------------------------------------------------------------------
 Emoncms - open source energy visualisation
 Part of the OpenEnergyMonitor project:
 http://openenergymonitor.org
 */

// no direct access
defined('EMONCMS_EXEC') or die('Restricted access');

function app_controller()
{
    global $path,$session,$route,$fullwidth;

    $fullwidth = true;
    $result = "#UNDEFINED#";
    $apikey="xxxxxxxxxxxxxxxxxx";

    require_once "Modules/app/app_model.php";
    $appconfig = new AppConfig(false, false);
    $appavail = $appconfig->get_available();

    if ($route->action == "view") {
        // enable apikey read access
        /*$userid = false;
        if (isset($session['write']) && $session['write']) {
            $userid = $session['userid'];
            $apikey = $user->get_apikey_write($session['userid']);
        } else if (isset($_GET['readkey'])) {
            $apikey = $_GET['readkey'];
            $userid = $user->get_id_from_apikey($apikey);
        } else if (isset($_GET['apikey'])) {
            $apikey = $_GET['apikey'];
            $userid = $user->get_id_from_apikey($apikey);
        }*/
        
        if ($session["read"])
        {
            $session["write"] = 0;
            
            $remoteaccess = new RemoteAccess($session["username"],$session["password"]);
            $applist = $remoteaccess->request("app/list",array());
            
            if ($route->subaction) {
                $app = $route->subaction;
            } else {
                $app = get("name");
            }
            
            if (!isset($applist->$app)) {
                foreach ($applist as $key=>$val) { $app = $key; break; }
            }
            
            $route->format = "theme";
            if ($app!=false) {
                $id = $applist->$app->app;
                if (isset($appavail[$id])) {
                    $dir = $appavail[$id]['dir'];
                    $config = $applist->$app->config;
                }
                else {
                    $id = 'blank';
                    $dir = "Modules/app/apps/blank/";
                    $config = new stdClass();
                }
            }
            
            $result = "<link href='".$path."Modules/app/Views/css/pagenav.css?v=1' rel='stylesheet'>";
            $result .= "<div id='wrapper'>";
            if ($session['read']) $result .= view("Modules/app/Views/app_sidebar.php",array("applist"=>$applist));
            if ($app!=false) {
                $result .= view($dir.$id.".php",array("name"=>$app, "appdir"=>$dir, "config"=>$config, "apikey"=>$apikey));
            } else {
                $result .= view("Modules/app/Views/app_view.php",array("apps"=>$appavail));
            }
            $result .= "</div>";
        }
    }
    else if ($route->action == "dataremote") {
        $route->format = "json";
        $id = (int) get("id");
        $start = (float) get("start");
        $end = (float) get("end");
        $interval = (int) get("interval");
        $result = json_decode(file_get_contents("http://emoncms.org/feed/data.json?id=$id&start=$start&end=$end&interval=$interval&skipmissing=0&limitinterval=0"));
    }
    else if ($route->action == "valueremote") {
        $route->format = "json";
        $id = (int) get("id");
        $result = (float) json_decode(file_get_contents("http://emoncms.org/feed/value.json?id=$id"));
    }
    else if ($route->action == "ukgridremote") {
        $route->format = "json";
        $start = (float) get("start");
        $end = (float) get("end");
        $interval = (int) get("interval");
        $result = json_decode(file_get_contents("https://openenergymonitor.org/ukgrid/api.php?q=data&id=1&start=$start&end=$end&interval=$interval"));
    }

    return array('content'=>$result, 'fullwidth'=>$fullwidth);
}
