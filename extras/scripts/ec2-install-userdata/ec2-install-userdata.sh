#!/bin/bash -ex
exec > >(tee /var/log/user-data.log|logger -t user-data -s 2>/dev/console) 2>&1

# install ThinkUp on EC2 Ubuntu instance:
#
# @spara 12/23/10
# @waxpancake 1/3/11

# install required packages
sudo apt-get update && apt-get upgrade -y
sudo apt-get -y install apache2 php5-mysql libapache2-mod-php5 

sudo apt-get -y install unzip  
sudo apt-get -y install curl libcurl3 libcurl3-dev php5-curl php5-mcrypt php5-gd --fix-missing
sudo apt-get -y install sendmail

# Install MySQL directly on instance
sudo DEBIAN_FRONTEND=noninteractive apt-get install -q -y mysql-server 

# RDS - Would you rather use AWS RDS instead? 
# Comment out the line above that install MySQL Server
# and uncomment the line below
#sudo apt-get -y install mysql-client


# Optional Section (not necessary but nice to haves)
## Packages  
## phpmyadmin => Web based MySQL client powered by PHP
## htop => enhanced version of top
## byobu => screen wrapper that can be configure to track approx. cost of EC2 instance
## aptitude => high-level interface to the package manager
## Uncomment line below to install, remove any packages you don't want
#sudo apt-get -y install  phpmyadmin htop byobu aptitude

# #Apache Best Practice" Configuration
# Disable directory indexing (uncomment line below)
#a2dismod autoindex

# restart apache to init php packages
sudo service apache2 restart


wget https://github.com/downloads/ginatrapani/ThinkUp/thinkup-0.8.zip --no-check-certificate
sudo unzip -d /var/www/ thinkup-0.8.zip

# config thinkup installer
sudo ln -s /usr/sbin/sendmail /usr/bin/sendmail
sudo chown -R www-data /var/www/thinkup/_lib/view/compiled_view/
sudo touch /var/www/thinkup/config.inc.php
sudo chown www-data /var/www/thinkup/config.inc.php

# create database

mysqladmin -u root password NEWPASSWORDHERE 
mysqladmin -h localhost -u root -pNEWPASSWORDHERE create thinkup

# add apparmor exception for ThinkUp backup
sudo sed -i '
/\/var\/run\/mysqld\/mysqld.sock w,/ a\
  /var/www/thinkup/_lib/view/compiled_view/** rw,
' /etc/apparmor.d/usr.sbin.mysqld
sudo /etc/init.d/apparmor restart