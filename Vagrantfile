# -*- mode: ruby -*-
# vi: set ft=ruby :

$software = <<SCRIPT
# Downgrade to PHP 7.1
apt-add-repository -y ppa:ondrej/php
apt-get -yq update
apt-get -yq install php7.1

# Install MYSQL
debconf-set-selections <<< "mysql-server mysql-server/root_password password root"
debconf-set-selections <<< "mysql-server mysql-server/root_password_again password root"
apt-get -yq install mysql-server

# Install required PHP packages
apt-get -yq install php7.1-dom
apt-get -yq install php7.1-mbstring
apt-get -yq install php7.1-intl
apt-get -yq install php7.1-gd
apt-get -yq install php7.1-mcrypt
apt-get -yq install php7.1-curl
apt-get -yq install php7.1-zip
apt-get -yq install php7.1-mysql

# Install Java
apt-get -yq install openjdk-11-jdk

# Install required tools
apt-get -yq install libxml2-utils
apt-get -yq install pandoc
apt-get -yq install ant

# Install Composer
cd /vagrant
curl -s http://getcomposer.org/installer | php
SCRIPT

$composer = <<SCRIPT
cd /vagrant
php composer.phar update
SCRIPT

$solr = <<SCRIPT
cd /home/vagrant
mkdir "downloads"
cd downloads
SOLR_TAR="solr-7.7.2.tgz"
if test ! -f "$SOLR_TAR"; then
  wget -q "https://archive.apache.org/dist/lucene/solr/7.7.2/$SOLR_TAR"
fi
tar xfz "$SOLR_TAR" -C /home/vagrant
cd /home/vagrant/solr-7.7.2
mkdir -p server/solr/opus4/conf
echo name=opus4 > server/solr/opus4/core.properties
cd server/solr/opus4/conf/
ln -s /vagrant/vendor/opus4-repo/search/conf/schema.xml schema.xml
ln -s /vagrant/vendor/opus4-repo/search/conf/solrconfig.xml solrconfig.xml
cd /home/vagrant/solr-7.7.2
./bin/solr start
SCRIPT

$database = <<SCRIPT
export MYSQL_PWD=root && mysql --default-character-set=utf8 -h 'localhost' -P '3306' -u 'root' -v -e "CREATE DATABASE IF NOT EXISTS opusdb DEFAULT CHARACTER SET = UTF8 DEFAULT COLLATE = UTF8_GENERAL_CI; CREATE USER IF NOT EXISTS 'opus4admin'@'localhost' IDENTIFIED WITH mysql_native_password BY 'root'; GRANT ALL PRIVILEGES ON opusdb.* TO 'opus4admin'@'localhost'; CREATE USER IF NOT EXISTS 'opus4'@'localhost' IDENTIFIED WITH mysql_native_password BY 'root'; GRANT SELECT,INSERT,UPDATE,DELETE ON opusdb.* TO 'opus4'@'localhost'; FLUSH PRIVILEGES;"
SCRIPT

$apache = <<SCRIPT
cd /vagrant/apacheconf
if test ! -f "apache.conf"; then
  cp apache24.conf.template apache.conf
  OPUS_URL_BASE="/opus4"
  BASEDIR="/vagrant"
  sed -e "s!/OPUS_URL_BASE!/$OPUS_URL_BASE!g; s!/BASEDIR/!/$BASEDIR/!; s!//*!/!g" "apache24.conf.template" > "apache.conf"
fi
ln -s /vagrant/apacheconf/apache.conf /etc/apache2/sites-available/opus4.conf
a2enmod rewrite
a2ensite opus4
service apache2 restart
SCRIPT

$opus = <<SCRIPT
cd /vagrant
ant prepare-workspace prepare-test-workspace prepare-config -DdbUserPassword=root -DdbAdminPassword=root
SCRIPT

$testdata = <<SCRIPT
cd /vagrant
ant reset-testdata
SCRIPT

$fix = <<SCRIPT
cd /vagrant
bin/set-file-permissions.sh
SCRIPT

$environment = <<SCRIPT
if ! grep "cd /vagrant" /home/vagrant/.profile > /dev/null; then
  echo "cd /vagrant" >> /home/vagrant/.profile
fi
SCRIPT

$start = <<SCRIPT
service apache2 reload
cd /home/vagrant/solr-7.7.2
./bin/solr start
SCRIPT

Vagrant.configure("2") do |config|
  config.vm.box = "bento/ubuntu-20.04"

  config.vm.synced_folder "workspace", "/vagrant/workspace", group: "www-data", create: true

  config.vm.network "forwarded_port", guest: 80, host: 8080, host_ip: "127.0.0.1"
  config.vm.network "forwarded_port", guest: 8983, host: 9983, host_ip: "127.0.0.1"

  config.vm.provision "Install required software...", type: "shell", inline: $software
  config.vm.provision "Install Composer dependencies...", type: "shell", privileged: false, inline: $composer
  config.vm.provision "Install Apache Solr...", type: "shell", privileged: false, inline: $solr
  config.vm.provision "Create database...", type: "shell", inline: $database
  config.vm.provision "Configure OPUS 4...", type: "shell", privileged: false, inline: $opus
  config.vm.provision "Setup site in Apache2...", type: "shell", inline: $apache
  config.vm.provision "Initialize test data...", type: "shell", privileged: false, inline: $testdata
  config.vm.provision "Fix permissions...", type: "shell", inline: $fix
  config.vm.provision "Setup environment...", type: "shell", inline: $environment
  config.vm.provision "Start services...", type: "shell", run: "always", inline: $start
end
