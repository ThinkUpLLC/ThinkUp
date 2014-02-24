#!/bin/sh

sudo cp extras/dev/travis/my-override.cnf /etc/mysql/conf.d/
sudo service mysql restart
mysql -e 'create database thinkup'
cp extras/dev/config/config.inc.php webapp/config.inc.php
cp extras/dev/config/config.tests.inc.php tests/config.tests.inc.php
mkdir webapp/data/sessions/
chmod -R 777 webapp/data
chmod -f -R 777 build
mkdir webapp/data/logs/

touch webapp/data/logs/stream.log
touch webapp/data/logs/crawler.log
sudo apt-get install apache2 libapache2-mod-fastcgi
export PHPV=$(phpenv version-name)
echo "Running PHP = $PHPV"
echo "cgi.fix_pathinfo = 1" >> ~/.phpenv/versions/$PHPV/etc/php.ini
if [ "$PHPV" = "5.2" ]; then
    echo "I'm in here."
    sudo sh -c "echo 'error_reporting = 22527' >> ~/.phpenv/versions/$PHPV/etc/php.ini"
    sudo sh -c "echo 'session.save_path = "$(pwd)/webapp/data/sessions"' >> ~/.phpenv/versions/$PHPV/etc/php.ini"
    sudo a2enmod rewrite actions alias
    sudo cp -f extras/dev/travis/travis-ci-apache-php52 /etc/apache2/sites-available/default
    sudo sed -e "s?%PHPPATH%?/home/travis/.phpenv/versions/5.2/bin?g" --in-place /etc/apache2/sites-available/default
else
    sudo cp ~/.phpenv/versions/$PHPV/etc/php-fpm.conf.default ~/.phpenv/versions/$PHPV/etc/php-fpm.conf
    sudo sh -c "echo 'php_value[error_reporting] = 22527' >> ~/.phpenv/versions/$PHPV/etc/php-fpm.conf"
    sudo sh -c "echo 'php_value[session.save_path] = "$(pwd)/webapp/data/sessions"' >> ~/.phpenv/versions/$PHPV/etc/php-fpm.conf"
    echo "cgi.fix_pathinfo = 1" >> ~/.phpenv/versions/$PHPV/etc/php.ini
    ~/.phpenv/versions/$PHPV/sbin/php-fpm
    sudo a2enmod rewrite actions fastcgi alias
    sudo cp -f extras/dev/travis/travis-ci-apache /etc/apache2/sites-available/default
fi

sudo sed -e "s?%TRAVIS_BUILD_DIR%?$(pwd)/webapp?g" --in-place /etc/apache2/sites-available/default
sudo service apache2 restart
