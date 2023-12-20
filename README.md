# [NUT](http://dezrin.ddns.net:8888/)![image info](http://dezrin.ddns.net:8080/api/public/dl/Z-WXwfVe/share/Screenshot%202023-12-14%20234942.png)
[![Build Status](http://dezrin.ddns.net:8080/api/public/dl/gxyJXCDG/share/release-passing.svg)](https://github.com/Dezrin/nut/releases/tag/v1.00.5)

PHP Interface for Network UPS Tools

## Prerequisites
1. Ubuntu Server 20.04 running on a VPS/VM with at least 1vCPU and 2GB RAM, 50GB storage.
2. [LAMP](https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-20-04) stack installed and configured with PHP7.4 minimum
3. git Installed (apt install git)
4. Install Network UPS Tools `NUT` as described below:

# Install [NUT](https://networkupstools.org/)
```sh
apt-get install nut
nano /etc/nut/ups.conf
```
*Paste the foollowing at the bottom. Mine’s an Eaton3s 550, so I’ve set it to a recognizable name (eaton3s)*
```sh
[eaton3s]
driver = usbhid-ups
port = auto
```
## Create the following directories and reboot machine
```sh
mkdir /var/run/nut
chown root:nut /var/run/nut
chmod 770 /var/run/nut
```

## Start NUT
```sh upsdrvctl start ```

*Should give the following output*
```sh
Network UPS Tools - UPS driver controller 2.4.3
Network UPS Tools - Generic HID driver 0.34 (2.4.3)
USB communication driver 0.31
Using subdriver: EATON HID 0.95
```
## Setup NUT to listen on Port 3493
```sh
nano /etc/nut/upsd.conf
``` 
*Add the following lines where <IPADDRESS> is the IP of your machine*
```sh
LISTEN 127.0.0.1 3493
LISTEN ::1 3493
LISTEN <IPADDRESS> 3493
```
## Set the mode
```sh
nano /etc/nut/nut.conf:
```
*Enter the following:*
```sh
MODE=netserver
```
## Start the network data server
```sh 
upsd
```
## Check the status
```sh
upsc eaton3slocalhost ups.status
```
*Should output the following*
```sh 
OL
```
**OL means your system is running On Line power. If you want to see all the info, try this instead**

## Setup users to access the info and make changes.
```sh
nano /etc/nut/upsd.users
```
*Add monitor master user and a monitor slave user for remote machines*
```sh
[monuser]
    password = <PASSWORD_REPLACE>
    actions = SET FSD
    instcmds = ALL
    upsmon master
```
Reload upsd
```sh
upsd -c reload
```

## Setup upsmon for our machine
```sh 
nano /etc/nut/upsmon.conf
```
*Paste the following*
```sh
MONITOR eaton3s@localhost 1 local_mon <PASSWORD_REPLACE> master
```

# Installation of NUT Web Client

```sh
git clone https://github.com/Dezrin/nut.git
cp nut-*/* /var/www/html/
```

```sh
cd /var/www/html
```

Edit the ```config.php``` file with your NUT details

```sh
nano config.php
```

Ensure these are the same settings that you configured NUT as above

```sh
    'port' => '3493',               /* Port of NUT Server */
    'server' => '10.20.10.191',        /* NUT Server */
    'ups_name' => 'eaton3s',            /* UPS name configured in ups.conf */
```

Head to your VM's IP address in your web browser: http://127.0.0.1/ as an example. 

This will now load your UPS information and display it on the web. 
