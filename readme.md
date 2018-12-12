# Emoncms Remote Access

## Client Installation

Install python dependencies:

    pip install python-dotenv

Install and start remoteaccess service:

    sudo ln -s /home/pi/remoteaccess/remoteaccess.service /lib/systemd/system
    sudo systemctl enable remoteaccess.service
    sudo systemctl start demandshaper
    
View service log:

    journalctl -f -u remoteaccess -n 100
