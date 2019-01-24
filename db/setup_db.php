<?php

require "../settings.php";
require "dbschemasetup.php";
$mysqli = new mysqli($mysql_settings['host'],$mysql_settings['user'],$mysql_settings['password'],$mysql_settings['database']);

require "schema.php";

print "----------------------------------\n";
print "Running database setup/update tool\n";
print "----------------------------------\n";

$result = db_schema_setup($mysqli,$schema,true);

foreach ($result as $line) {
    print $line."\n";
}
