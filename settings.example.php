<?php
// mqtt broker web socket connection
$settings = array(
    'host' => 'wss://mqtt.emoncms.org',
    'port' => 8083,
    'tls' => true
);

// Save As settings.php for production
// Save As settings.dev.php for local development