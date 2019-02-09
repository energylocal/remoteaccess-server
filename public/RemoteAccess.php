<?php

class RemoteAccess
{
    // MQTT Client
    private $client;
    private $state;
    private $result;
    private $request;
    // Session
    private $username;
    private $password;
    private $clientId;
    

    public function __construct($username,$password)
    {
        $this->username = $username;
        $this->password = $password;
    }
    
    public function request($controller,$action,$subaction,$params)
    {
        $this->result = false;
        $this->clientId = "mqtt_".$this->username."_".rand(0,1024);
        
        if (isset($params["q"])) unset($params["q"]);
        if (isset($params["apikey"])) unset($params["apikey"]);

        $this->request = array(
            "clientId"=>$this->clientId, "controller"=>$controller, "action"=>$action, "subaction"=>$subaction, "data"=>$params
        );

        $this->client = new Mosquitto\Client('emoncms',true);
        
        $this->client->onConnect(function($r, $message){
            $this->connect($r, $message);
        });
        $this->client->onDisconnect(function(){
            $this->disconnect();
        });
        $this->client->onSubscribe(function(){
            $this->subscribe();
        });
        $this->client->onMessage(function($message){
            $this->message($message);
        });
                   
        $this->state = 0; // 0: startfetch
                    // 1: connected
                    // 2: subscribed
                    // 3: complete

        $this->client->setCredentials($this->username,$this->password);
        $this->client->connect("localhost", 1883, 5);
               
        $start = time();
        while((time()-$start)<10.0) {
            try { 
                $this->client->loop(10); 
            } catch (Exception $e) {
                if ($this->state) return "error: ".$e;
            }
            
            if ((time()-$start)>=3.0) {
                $this->client->disconnect();
            }
            
            if ($this->state==3) break;
        }
        
        if ($this->result) {
            $result = json_decode($this->result);
            return $result->result;
        } else {
            return "API Timeout";
        }
    }
    
    private function connect($r, $message) {
        if( $r==0 ) {
            $this->state = 1;
            $this->client->subscribe("user/".$this->username."/response/".$this->clientId,2);
        } else {
            $this->client->disconnect();
        }
    }

    private function subscribe() {
        $this->client->publish("user/".$this->username."/request", json_encode($this->request));
    }

    private function unsubscribe() {
        $this->state = 1;
    }

    private function disconnect() {
        $this->state = 3;
    }

    private function message($message) {
        $topic = $message->topic;
        $this->result = $message->payload;
        $this->client->disconnect();
    }
}
