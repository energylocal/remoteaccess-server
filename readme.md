# Emoncms Remote Access

Background discussion: [https://community.openenergymonitor.org/t/emoncms-local-vs-remote/7268](https://community.openenergymonitor.org/t/emoncms-local-vs-remote/7268)

## Login on mqtt.emoncms.org

Login on mqtt.emoncms.org with your emoncms.org username and password to register for the remote access service.

https://mqtt.emoncms.org

## Client Installation

Install python dependencies:

    pip install python-dotenv

Install and start remoteaccess service:

    sudo ln -s /home/pi/remoteaccess/remoteaccess.service /lib/systemd/system
    sudo systemctl enable remoteaccess.service
    sudo systemctl start remoteaccess
    
View service log:

    journalctl -f -u remoteaccess -n 100

Create .env settings file with emoncms.org username and password.
