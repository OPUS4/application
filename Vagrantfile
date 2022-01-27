# -*- mode: ruby -*-
# vi: set ft=ruby :

$software = <<SCRIPT
# Downgrade to PHP 7.1
apt-add-repository -y ppa:ondrej/php
apt-get -yq update
apt-get -yq install php7.1

# Install required PHP packages
apt-get -yq install php7.1-dom
apt-get -yq install php7.1-mbstring
apt-get -yq install php7.1-intl
apt-get -yq install php7.1-gd
apt-get -yq install php7.1-mcrypt
apt-get -yq install php7.1-curl
apt-get -yq install php7.1-zip
apt-get -yq install php7.1-mysql

# Install required tools
apt-get -yq install libxml2-utils
apt-get -yq install pandoc
apt-get -yq install composer
SCRIPT

$composer = <<SCRIPT
cd /vagrant
composer install
SCRIPT

$solr = <<SCRIPT
mkdir "downloads"
cd downloads
wget -q "https://archive.apache.org/dist/lucene/solr/7.7.2/solr-7.7.2.tgz"
# TODO create and configure core (dependes on Composer dependencies)
SCRIPT

$database = <<SCRIPT
# TODO setup database
SCRIPT

$apache = <<SCRIPT
# TODO create site config
a2enmod rewrite
service apache2 restart
SCRIPT

$opus = <<SCRIPT
# TODO ant prepare-workspace prepare-test-workspace prepare-config reset-testdata -DdbUserPassword=root -DdbAdminPassword=root
SCRIPT

$testdata = <<SCRIPT
cd /vagrant
# TODO ant reset-testdata
SCRIPT

$environment = <<SCRIPT
if ! grep "cd /vagrant" /home/vagrant/.profile > /dev/null; then
  echo "cd /vagrant" >> /home/vagrant/.profile
fi
SCRIPT

Vagrant.configure("2") do |config|
  config.vm.box = "bento/ubuntu-20.04"

  config.vm.network "forwarded_port", guest: 80, host: 8080, host_ip: "127.0.0.1"

  config.vm.provision "Install required software...", type: "shell", inline: $software
  config.vm.provision "Install Composer dependencies...", type: "shell", privileged: false, inline: $composer
  config.vm.provision "Install Apache Solr...", type: "shell", privileged: false, inline: $solr
  config.vm.provision "Setup Apache2...", type: "shell", inline: $apache
  config.vm.provision "Initialize test data...", type: "shell", privileged: false, inline: $testdata
  config.vm.provision "Setup environment...", type: "shell", inline: $environment
end
