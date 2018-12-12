# Emoncms Remote Access

Background discussion: [https://community.openenergymonitor.org/t/emoncms-local-vs-remote/7268](https://community.openenergymonitor.org/t/emoncms-local-vs-remote/7268)

## Client Installation

Install python dependencies:

    pip install python-dotenv

Install and start remoteaccess service:

    sudo ln -s /home/pi/remoteaccess/remoteaccess.service /lib/systemd/system
    sudo systemctl enable remoteaccess.service
    sudo systemctl start remoteaccess
    
View service log:

    journalctl -f -u remoteaccess -n 100
