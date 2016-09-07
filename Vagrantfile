#
# Vagrantfile for Thinkup
# Builds a precise64 box, installs ThinkUp SQL, and creates a default user.
# @author woganmay
#
# Default user:
#     email: vagrant@vagrant.local
#  password: thinkup00
#

VAGRANTFILE_API_VERSION = "2"

# Setup!
$script = <<SCRIPT

# Install stuff in noninteractive mode
export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get install -y sudo apache2 php5 mysql-server php5-mysql php5-curl
export DEBIAN_FRONTEND=

# Will be used as the mysql root password
export ADMINPASS="$(date | md5sum | head -c 16)"
echo $ADMINPASS > /etc/mysql_root_password

# Set MySQL root
mysqladmin -u root password "$ADMINPASS"
mysqladmin -u root -h localhost password "$ADMINPASS"
service mysql restart

# Create database, set access and import the sql build
mysql -uroot -p$ADMINPASS -e 'CREATE DATABASE thinkup;'
mysql -uroot -p$ADMINPASS -e "GRANT ALL ON thinkup.* TO 'thinkup_sql'@'localhost' IDENTIFIED BY 'thinkup_password';"
mysql -uroot -p$ADMINPASS thinkup < /vagrant/webapp/install/sql/build-db_mysql.sql

# Create the default admin user (Email: vagrant@vagrant.local, Pass: thinkup00)
mysql -uroot -p$ADMINPASS -e 'INSERT INTO thinkup.tu_owners (`email`, `pwd`, `pwd_salt`, `joined`, `activation_code`, `full_name`, `timezone`, `api_key`, `is_admin`, `is_activated`) VALUES ("vagrant@vagrant.local", "7876ece7604da1df99ca2adce144c2cfeeee5679a987365fbf759203775e5c27", "b51838bc616ac039e36899426be4b5973b906a6277433c3ab1ed9076efe4b53b", NOW(), 1234, "Vagrant User", "UTC", "e46b19a0b38007b684efefe3aedb82aa", 1, 1)'

# Configure apache to serve /vagrant/ as a default site
a2dissite default
cp /vagrant/vagrant-vhost.conf /etc/apache2/sites-available/vagrant
a2ensite vagrant
a2enmod rewrite
service apache2 restart

SCRIPT

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = "base"
  config.vm.box_url = "http://files.vagrantup.com/precise64.box"
  config.vm.provision "shell", inline: $script
  config.vm.network "forwarded_port", guest: 80, host: 8080
  config.vm.synced_folder "webapp/data/", "/thinkup-data", owner: "www-data", group: "www-data"
end