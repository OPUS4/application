#!/usr/bin/env bash
#
# LICENCE
# This code is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This code is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# @copyright   Copyright (c) 2018-2022, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
#

#
# Script for creating OPUS 4 database.
#
# As part of the installation this script creates a database using the information
# provided in the OPUS 4 configuration files.
#
# Parameters can be used to specify a different database name and other information.
#
# TODO move to framework (SQL for creating database defined twice in framework and here)
#

set -e

MYSQL_CLIENT='/usr/bin/mysql'

SCRIPT_NAME="`basename "$0"`"
SCRIPT_NAME_FULL="`readlink -f "$0"`"
SCRIPT_PATH="`dirname "$SCRIPT_NAME_FULL"`"
BASEDIR="$(dirname "$SCRIPT_PATH")"

# Parse command line options

while getopts ":c:" opt; do
  case $opt in
    c) OPUS_CONF="$OPTARG"
    ;;
  esac
done

OPUS_CONF="${OPUS_CONF:-config.ini}"
OPUS_CONSOLE_CONF="${OPUS_CONSOLE_CONF:-console.ini}"

#
# Prompt for database parameters
#

echo
echo "Database configuration"
echo

[[ -z $DBNAME ]] && read -p "New OPUS Database Name [opusdb]: "           DBNAME
[[ -z $DB_ADMIN ]] && read -p "New OPUS Database Admin Name [opus4admin]: " DB_ADMIN

while [[ -z $DB_ADMIN_PASSWORD || "$DB_ADMIN_PASSWORD" != "$DB_ADMIN_PASSWORD_VERIFY" ]] ;
do
  read -p "New OPUS Database Admin Password: " -s       DB_ADMIN_PASSWORD
  echo
  read -p "New OPUS Database Admin Password again: " -s DB_ADMIN_PASSWORD_VERIFY
  echo
  if [[ $DB_ADMIN_PASSWORD != $DB_ADMIN_PASSWORD_VERIFY ]] ;
  then
    echo "Passwords do not match. Please try again."
  fi
done

[[ -z $DB_USER ]] && read -p "New OPUS Database User Name [opus4]: "       DB_USER

while [[ -z $DB_USER_PASSWORD || "$DB_USER_PASSWORD" != "$DB_USER_PASSWORD_VERIFY" ]] ;
do
  read -p "New OPUS Database User Password: " -s        DB_USER_PASSWORD
  echo
  read -p "New OPUS Database User Password again: " -s  DB_USER_PASSWORD_VERIFY
  echo
  if [[ $DB_USER_PASSWORD != $DB_USER_PASSWORD_VERIFY ]] ;
  then
    echo "Passwords do not match. Please try again."
  fi
done

# set defaults if values are not given
DBNAME="${DBNAME:-opusdb}"
DB_ADMIN="${DB_ADMIN:-opus4admin}"
DB_USER="${DB_USER:-opus4}"

# escape ! (for later use in sed substitute)
DBNAME_ESC="${DBNAME//\!/\\\!}"
DB_ADMIN_ESC="${DB_ADMIN//\!/\\\!}"
DB_ADMIN_PASSWORD_ESC="${DB_ADMIN_PASSWORD//\!/\\\!}"
DB_USER_ESC="${DB_USER//\!/\\\!}"
DB_USER_PASSWORD_ESC="${DB_USER_PASSWORD//\!/\\\!}"

#
# Create database and users.
#
# By default the database and the users are created requiring the MySQL root password,
# however that can be suppressed in order to just generate the configuration files for
# an existing database.
#

echo
echo "Please provide parameters for the database connection:"
[[ -z $MYSQLHOST ]] && read -p "MySQL DBMS Host [localhost]: " MYSQLHOST
[[ -z $MYSQLPORT ]] && read -p "MySQL DBMS Port [3306]: "      MYSQLPORT

# set defaults if value is not given
MYSQLHOST="${MYSQLHOST:-localhost}"
MYSQLPORT="${MYSQLPORT:-3306}"

# escape ! (for later use in sed substitute)
MYSQLHOST_ESC="${MYSQLHOST//\!/\\\!}"
MYSQLPORT_ESC="${MYSQLPORT//\!/\\\!}"

#
# Create config.ini and set database related parameters.
#
# TODO overwrite existing file?
#

cd "$BASEDIR/application/configs"
cp config.ini.template "$OPUS_CONF"
if [ localhost != "$MYSQLHOST" ]; then
  sed -i -e "s!^; db.params.host = localhost!db.params.host = '$MYSQLHOST_ESC'!" "$OPUS_CONF"
fi
if [ 3306 != "$MYSQLPORT" ]; then
  sed -i -e "s!^; db.params.port = 3306!db.params.port = '$MYSQLPORT_ESC'!" "$OPUS_CONF"
fi
sed -i -e "s!@db.user.name@!'$DB_USER_ESC'!" \
       -e "s!@db.user.password@!'$DB_USER_PASSWORD_ESC'!" \
       -e "s!@db.name@!'$DBNAME_ESC'!" "$OPUS_CONF"

# Add admin credentials to configuration for command line scripts
cp console.ini.template "$OPUS_CONSOLE_CONF"

sed -i -e "s!@db.admin.name@!'$DB_ADMIN_ESC'!" \
       -e "s!@db.admin.password@!'$DB_ADMIN_PASSWORD_ESC'!" "$OPUS_CONSOLE_CONF"

#
# Optionally initialize database.
#

[[ -z $CREATE_DATABASE ]] && read -p "Create database and users [Y]? " CREATE_DATABASE

if [[ -z "$CREATE_DATABASE" || "$CREATE_DATABASE" == Y || "$CREATE_DATABASE" == y ]] ;
then

    echo
    [[ -z $MYSQLROOT ]] && read -p "MySQL Root User [root]: "                    MYSQLROOT
    read -p "MySQL Root User Password: " -s MYSQLROOT_PASSWORD
    echo

    # set defaults if value is not given
    MYSQLROOT="${MYSQLROOT:-root}"

    # prepare to access MySQL service
    MYSQL_OPTS=""
    [ "localhost" != "$MYSQLHOST" ] && MYSQL_OPTS="-h $MYSQLHOST"
    [ "3306" != "$MYSQLPORT" ] && MYSQL_OPTS="$MYSQL_OPTS -P $MYSQLPORT"

    #
    # Create database and users in MySQL.
    #
    # Users do not have to be created first before granting privileges.
    #

mysqlRoot() {
  "$MYSQL_CLIENT" --defaults-file=<(echo -e "[client]\npassword=${MYSQLROOT_PASSWORD}") --default-character-set=utf8mb4 ${MYSQL_OPTS} -u "$MYSQLROOT" -v
}

mysqlRoot <<LimitString
CREATE DATABASE IF NOT EXISTS $DBNAME DEFAULT CHARACTER SET = UTF8MB4 DEFAULT COLLATE = UTF8MB4_UNICODE_CI;
CREATE USER '$DB_ADMIN'@'$MYSQLHOST' IDENTIFIED WITH mysql_native_password BY '$DB_ADMIN_PASSWORD';
GRANT ALL PRIVILEGES ON $DBNAME.* TO '$DB_ADMIN'@'$MYSQLHOST';
CREATE USER '$DB_USER'@'$MYSQLHOST' IDENTIFIED WITH mysql_native_password BY '$DB_USER_PASSWORD';
GRANT SELECT,INSERT,UPDATE,DELETE ON $DBNAME.* TO '$DB_USER'@'$MYSQLHOST';
FLUSH PRIVILEGES;
LimitString

    #
    # Create database schema
    #

    php "$BASEDIR/db/createdb.php"

fi
