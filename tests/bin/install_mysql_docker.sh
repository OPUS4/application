#!/usr/bin/env bash

# Install MySQL with preconfig MySQL-password
echo "mysql-server-5.5 mysql-server/root_password password root" | debconf-set-selections \
    && echo "mysql-server-5.5 mysql-server/root_password_again password root" | debconf-set-selections \
    && apt-get -y install mysql-server

# Start MySQL
sudo service mysql start

# Create DB and user -> have to be in one line
export MYSQL_PWD=root \
    && mysql --default-character-set=utf8 -h 'localhost' -P '3306' -u 'root' -v -e "CREATE DATABASE IF NOT EXISTS opusdb DEFAULT CHARACTER SET = UTF8 DEFAULT COLLATE = UTF8_GENERAL_CI; CREATE USER 'opus4admin'@'localhost' IDENTIFIED BY 'root'; GRANT ALL PRIVILEGES ON opusdb.* TO 'opus4admin'@'localhost'; CREATE USER 'opus4'@'localhost' IDENTIFIED BY 'root'; GRANT SELECT,INSERT,UPDATE,DELETE ON opusdb.* TO 'opus4'@'localhost'; FLUSH PRIVILEGES;"
