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

function remoteaccess_controller()
{
    global $session, $route, $remoteaccess_whitelist;
    // require "Modules/remoteaccess/RemoteAccess.php";
    
    // HTTP to MQTT bridge
    if ($session["write"]) {
        if (in_array($route->action."/".$route->subaction,$remoteaccess_whitelist)) {
            $route->format = "json";
            $remoteaccess = new RemoteAccess($session["username"],$session["password"]);
            return $remoteaccess->request($route->action,$route->subaction,$route->subaction2,$_GET);
        }
    }

    return array("content"=>"#UNDEFINED#");
}
