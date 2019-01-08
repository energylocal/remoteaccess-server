<?php

$session = array("username"=>"username","password"=>"password");
$session["clientId"] = "mqtt_".$session["username"]."_".rand(0,1024);

$request = array(
    "clientId"=>$session["clientId"], "action"=>"feed/list"
);

$result = false;

$mqtt_client = new Mosquitto\Client('emoncms',true);
$mqtt_client->onConnect('connect');
$mqtt_client->onDisconnect('disconnect');
$mqtt_client->onSubscribe('subscribe');
$mqtt_client->onMessage('message');
           
$state = 0; // 0: start
            // 1: connected
            // 2: subscribed
            // 3: complete

$mqtt_client->setCredentials($session["username"],$session["password"]);
$mqtt_client->connect("localhost", 1883, 5);
       
$start = time();
while((time()-$start)<10.0) {
    try { 
        $mqtt_client->loop(10); 
    } catch (Exception $e) {
        if ($state) { $result = "error: ".$e; break; }
    }
    
    if ((time()-$start)>=2.0) {
        $result = "timeout";
        $mqtt_client->disconnect();
    }
    
    if ($state==3) break;
}

print $result;

function connect($r, $message) {
    global $mqtt_client, $state, $session;
    if( $r==0 ) {
        $state = 1;
        $mqtt_client->subscribe("user/".$session["username"]."/response/".$session["clientId"],2);
    } else {
        $mqtt_client->disconnect();
    }
}

function subscribe() {
    global $mqtt_client, $session, $request;
    $mqtt_client->publish("user/".$session["username"]."/request", json_encode($request));
}

function unsubscribe() {
    global $state;
    $state = 1;
}

function disconnect() {
    global $state;
    $state = 3;
}

function message($message) {
    global $mqtt_client, $result;
    $topic = $message->topic;
    $result = $message->payload;
    $mqtt_client->disconnect();
}
