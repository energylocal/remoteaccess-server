# Emoncms Remote Access (Server)

Background discussion: [https://community.openenergymonitor.org/t/emoncms-local-vs-remote/7268](https://community.openenergymonitor.org/t/emoncms-local-vs-remote/7268)

## Login on mqtt.emoncms.org

Login on mqtt.emoncms.org with your emoncms.org username and password to register for the remote access service.

https://mqtt.emoncms.org

## MQTT Remote Access Server Setup

### Mosquitto setup

Mosquitto Installation

    sudo apt-add-repository ppa:mosquitto-dev/mosquitto-ppa
    sudo apt-get update
    sudo apt-get install mosquitto

Mosquitto MYSQL Auth (https://github.com/jpmens/mosquitto-auth-plug)

Download mosquitto source:

    git clone https://github.com/eclipse/mosquitto.git
    git checkout v1.4.8
    
Download mosquitto auth plugin:
    
    git clone https://github.com/jpmens/mosquitto-auth-plug.git
    git checkout 4e7fe9aadbdf6bcf9571b38d293bc0c081dd063b
    cd mosquitto-auth-plug
    cp config.mak.in config.mak
    
Configure:

    nano config.mak

Set:

    MOSQUITTO_SRC = /home/user/mosquitto
    OPENSSLDIR = /usr/sbin

Install libmysqlclient-dev:    
    
    sudo apt-get install libmysqlclient-dev

Make.
    
Add to /etc/mosquitto/mosquitto.conf

    # --------------------------------------------------------

    allow_anonymous false
    auth_plugin /home/user/mosquitto-auth-plug/auth-plug.so

    listener 1883 localhost

    # MQTTS
    listener 8883
    certfile /etc/letsencrypt/live/your_site/cert.pem
    cafile /etc/letsencrypt/live/your_site/chain.pem
    keyfile /etc/letsencrypt/live/your_site/privkey.pem

    # WSS Secure WebSockets
    listener 8083
    protocol websockets
    certfile /etc/letsencrypt/live/your_site/cert.pem
    cafile /etc/letsencrypt/live/your_site/chain.pem
    keyfile /etc/letsencrypt/live/your_site/privkey.pem

    # --------------------------------------------------------

    auth_opt_backends mysql

    auth_opt_host localhost
    auth_opt_port 3306
    auth_opt_dbname mysql_db
    auth_opt_user mysql_user
    auth_opt_pass mysql_password

    auth_opt_userquery SELECT pw FROM remoteaccess_users WHERE username = '%s'
    auth_opt_superquery SELECT COUNT(*) FROM remoteaccess_users WHERE username = '%s' AND super = 1
    auth_opt_aclquery SELECT topic FROM remoteaccess_acls WHERE (username = '%s') AND (rw >= %d)

    auth_opt_anonusername AnonymouS


Mosquitto PHP Client

    sudo apt-get install libmosquitto-dev
    sudo pecl install Mosquitto-alpha

---

### Emoncms Setup

1\) Install emoncms on remote server

    cd /var/www/
    git clone https://github.com/emoncms/emoncms.git
    
2\) Install remoteaccess-server repo to home folder on remote server

    cd /
    git clone https://github.com/emoncms/remoteaccess-server.git

3\) Symlink remoteaccess emoncms module

    cd remoteaccess-server
    ln -s /home/user/remoteaccess-server/remoteaccess-module /var/www/emoncms/Modules/remoteaccess


4\) Include RemoteAccess.php at top of index.php:

    require "Modules/remoteaccess/RemoteAccess.php";

5\) Add remote access code just above undefined content section:

    // HTTP to MQTT bridge
    if ($output["content"] === "#UNDEFINED#" && $session["write"]) {
        if (in_array($route->controller."/".$route->action,$remoteaccess_whitelist)) {
            $route->format = "json";
            $remoteaccess = new RemoteAccess($session["username"],$session["password"]);
            $output["content"] = $remoteaccess->request($route->controller,$route->action,$route->subaction,$_GET);
        }
    }

    // If no controller found or nothing is returned, give friendly error
    if ($output['content'] === "#UNDEFINED#") {

6\) Define $remoteaccess_whitelist in settings.php

    $remoteaccess_whitelist = array(
        "feed/list",
        "feed/data",
        "feed/value",
        "feed/timevalue",
        "feed/listwithmeta",
        "feed/fetch",
        "app/list",
        "device/list",
        "demandshaper/get",
        "demandshaper/submit",
        "input/get",
        "input/list"
    );

7\) On line 362 of Modules/user/user\_model.php just below the "if ($username\_out!=$username) ..." check, add the lines:

    //--------------------------------------------------------------------
    include "Modules/remoteaccess/remoteaccess_userlink.php";
    $result = remoteaccess_userlink($this->mysqli,$username,$password);
    if (!isset($result["success"]) || !$result["success"]) return $result;
    //--------------------------------------------------------------------

This code automatically registers a user account if the account already exists on emoncms.org and populates the remoteaccess users and acls table used by mosquitto.

8\) On line 399 of user_model.php add line:

    $_SESSION['password'] = $password;

The http to mqtt bridge requires the session password in order to authenticate the mqtt request. This line and the line below copies the password into the users session object for later use when making remote access api requests. It should be possible to avoid this step and use super user access.
    
8\) On line 218 of user_model.php add line:

    if (isset($_SESSION['password'])) $session['password'] = $_SESSION['password']; else $session['password'] = 'REMEMBER_ME';


## Preparing a module for remote access. E.g feed module:

1\) Comment out entire section that defines json API. We only need the section that provides the html view.

2\) Change:

    return array('content'=>'<br>Action not found');
    
to:

    return array('content'=>'#UNDEFINED#');
    
3\) Comment out input, feed & process model load at start of controller

Repeat this process for all other modules.
