<?php

$schema['remoteaccess_users'] = array(
    'id' => array('type' => 'int(11)', 'Null'=>false),
    'username' => array('type' => 'varchar(55)', 'Null'=>false),
    'pw' => array('type' => 'text', 'Null'=>false),
    'super' => array('type' => 'tinyint(1)', 'Null'=>false,'default'=>0)
);

$schema['remoteaccess_acls'] = array(
    'id' => array('type' => 'int(11)', 'Null'=>false, 'Key'=>'PRI', 'Extra'=>'auto_increment'),
    'username' => array('type' => 'varchar(55)'),
    'topic' => array('type' => 'varchar(55)'),
    'rw' => array('type' => 'tinyint(1)', 'Null'=>false,'default'=>1)
);
