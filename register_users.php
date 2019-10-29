<?php

define('EMONCMS_EXEC', 1);
chdir("/var/www/emoncms");
require "process_settings.php";
include "Modules/remoteaccess/remoteaccess_userlink.php";

// Connect to MYSQL
$mysqli = @new mysqli($server,$username,$password,$database,$port);
if ( $mysqli->connect_error ) {
    echo "Can't connect to database, please verify credentials/configuration in settings.php<br />";
    if ( $display_errors ) {
        echo "Error message: <b>" . $mysqli->connect_error . "</b>";
    }
    die();
}
// Set charset to utf8
$mysqli->set_charset("utf8");

$result_users = $mysqli->query("SELECT * FROM users");
while ($row = $result_users->fetch_object())
{
    $result = remoteaccess_userlink_existing($mysqli,$row->id);
}
