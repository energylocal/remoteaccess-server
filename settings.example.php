<?php
define('DEBUG', false);
/* 
0 = none
1 = log
2 = info
3 = debug
4 = verbose
5 = too much! - full stack trace for each log entry
*/
define('JS_LOG_LEVEL', 0);

// mqtt broker web socket connection
$settings = array(
    'host' => 'wss://mqtt.emoncms.org',
    'port' => 8083,
    'tls' => true
);

$mysql_settings = array(
    'host' => "mysql server hostname",
    'user' => "mysql server username",
    'password' => "mysql server password",
    'database' => "mysql server database_name",
    'port' => 3306
);

// Save As settings.php for production
// Save As settings.dev.php for local development
