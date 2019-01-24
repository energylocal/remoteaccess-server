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

function feed_controller()
{
    global $session, $route;
    global $feed_settings;
    $feed_settings['csvdownloadlimit_mb'] = 0;
    
    if ($route->action == "view" && $session['read']) {
        $route->format = 'theme';
        
        $ui_version_2 = true; //$user->get_preferences($session['userid'], 'deviceView');

        if (isset($ui_version_2) && $ui_version_2) {
            return view("Modules/feed/Views/feedlist_view_v2.php",array());
        } else {
            return view("Modules/feed/Views/feedlist_view.php",array());
        }
    }
    // else if ($route->action == "api" && $session['write']) return view("Modules/feed/Views/feedapi_view.php",array());

    return array('content'=>"#UNDEFINED#");
}
