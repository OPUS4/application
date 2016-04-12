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
# @author      Sascha Szott <szott@zib.de>
# @author      Jens Schwidder <schwidder@zib.de>
# @copyright   Copyright (c) 2010-2016, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License

#
# TODO more output to verify installation process
# TODO remmove the need to enter leading '/' for base url
# TODO add more explanatory output
# TODO fix solr setup
#

set -e

# START USER-CONFIGURATION

MYSQL_CLIENT='/usr/bin/mysql'

APACHE_CONF='apache.conf'
OPUS_CONF='config.ini'

# END OF USER-CONFIGURATION

SCRIPT_NAME="`basename "$0"`"
SCRIPT_NAME_FULL="`readlink -f "$0"`"
SCRIPT_PATH="`dirname "$SCRIPT_NAME_FULL"`"

BASEDIR="`dirname "$SCRIPT_PATH"`"

# check input parameter
if [ $# -eq 1 ]
then
    # check if selected OS is supported
    if [ "$1" = ubuntu ] || [ "$1" = suse ]
    then
      OS="$1"
    else
      echo "Invalid Argument $1 : use $SCRIPT_NAME {ubuntu,suse}"
      echo "Installation aborted."
      exit 1
    fi
else
    OS="ubuntu"
fi

cd "$BASEDIR"

cat <<LimitString

OPUS 4 Installation
===================

This script will ask you a number of questions in order to setup the following:

- Apache2 site configuration
- MySQL database schema
- Solr index

LimitString

# Ask for base Url for new OPUS 4 instance
[[ -z $OPUS_URL_BASE ]] && read -p "Base URL for OPUS [/opus4]: " OPUS_URL_BASE

OPUS_URL_BASE="${OPUS_URL_BASE:-/opus4}"

# Add leading '/' if missing
if [[ $OPUS_URL_BASE != /* ]] ;
then
   OPUS_URL_BASE="/$OPUS_URL_BASE"
fi

#
# Install Composer and dependencies
#

echo
echo "Installing Composer and dependencies ..."
echo

"$SCRIPT_PATH/install-composer.sh" "$BASEDIR"

#
# Prepare Apache2 configuration
#

echo
echo "Creating Apache2 site configuration ..."
echo

"$SCRIPT_PATH/install-apache.sh" "$OPUS_URL_BASE" "apache24.conf.template" "$APACHE_CONF" "$OS"

#
# Prompt for database parameters
#
# TODO Support using existing database
# TODO Support using existing users
#

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
  "$MYSQL_CLIENT" --defaults-file=<(echo -e "[client]\npassword=${MYSQLROOT_PASSWORD}") --default-character-set=utf8 ${MYSQL_OPTS} -u "$MYSQLROOT" -v
}

mysqlRoot <<LimitString
CREATE DATABASE IF NOT EXISTS $DBNAME DEFAULT CHARACTER SET = UTF8 DEFAULT COLLATE = UTF8_GENERAL_CI;
GRANT ALL PRIVILEGES ON $DBNAME.* TO '$DB_ADMIN'@'$MYSQLHOST' IDENTIFIED BY '$DB_ADMIN_PASSWORD';
GRANT SELECT,INSERT,UPDATE,DELETE ON $DBNAME.* TO '$DB_USER'@'$MYSQLHOST' IDENTIFIED BY '$DB_USER_PASSWORD';
FLUSH PRIVILEGES;
LimitString

    #
    # Create createdb.sh and set database related parameters
    #
    # TODO overwrite existing file?
    #

    cd "$BASEDIR/db"
    if [ ! -e createdb.sh ]; then
      cp createdb.sh.template createdb.sh
      if [ localhost != "$MYSQLHOST" ]; then
        sed -i -e "s!^# host=localhost!host='$MYSQLHOST_ESC'!" createdb.sh
      fi
      if [ 3306 != "$MYSQLPORT" ]; then
        sed -i -e "s!^# port=3306!port='$MYSQLPORT_ESC'!" createdb.sh
      fi
      sed -i -e "s!@db.admin.name@!'$DB_ADMIN_ESC'!" \
             -e "s!@db.admin.password@!'$DB_ADMIN_PASSWORD_ESC'!" \
             -e "s!@db.name@!'$DBNAME_ESC'!" createdb.sh

      bash createdb.sh || rm createdb.sh
    fi

fi

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

#
# Install and configure Solr search server
#
# Add Solr connection parameters to configuration files.
# Optionally install new local Solr.
#
# TODO add new core to existing, local Solr
#

# ask for desired port of solr service
[ -z "$SOLR_SERVER_PORT" ] && read -p "Solr server port number [8983]: " SOLR_SERVER_PORT
SOLR_SERVER_PORT="${SOLR_SERVER_PORT:-8983}"

cd "$BASEDIR"
[ -z "$INSTALL_SOLR" ] && read -p "Install Solr server? [Y]: " INSTALL_SOLR
if [ -z "$INSTALL_SOLR" ] || [ "$INSTALL_SOLR" = Y ] || [ "$INSTALL_SOLR" = y ] ;
then

  echo "Installing Apache Solr ..."
  # TODO call "$SCRIPT_PATH/install-solr.sh" "$SOLR_SERVER_PORT"

else
  # Do not install Solr, just configure connection

  # ask for host of solr service
  [ -z "$SOLR_SERVER_HOST" ] && read -p "Solr server host name [localhost]: " SOLR_SERVER_HOST
  SOLR_SERVER_HOST="${SOLR_SERVER_HOST:-localhost}"

  [ -z "$SOLR_CONTEXT" ] && read -p "Solr context name [/opus4]: " SOLR_CONTEXT
  SOLR_CONTEXT="${SOLR_CONTEXT:-/opus4}"

  # Text extraction can use a different Solr connection
  [ -z "$SOLR_EXTRACT" ] && read -p "Use different connection for text extraction? [N]: " SOLR_EXTRACT
  SOLR_EXTRACT="${SOLR_EXTRACT:-N}"

  if [ "$SOLR_EXTRACT" = Y ] || [ "$SOLR_EXTRACT" = y ] ;
  then
    [ -z "$SOLR_EXTRACT_SERVER_HOST" ] && read -p "Solr extraction server host [$SOLR_SERVER_HOST]: " SOLR_EXTRACT_SERVER_HOST

    [ -z "$SOLR_EXTRACT_SERVER_PORT" ] && read -p "Solr extraction server port [$SOLR_SERVER_PORT]: " SOLR_EXTRACT_SERVER_PORT

    [ -z "$SOLR_EXTRACT_CONTEXT" ] && read -p "Solr extraction server context [$SOLR_CONTEXT]: " SOLR_EXTRACT_CONTEXT
  fi
fi

# Use same connection if not set
SOLR_EXTRACT_SERVER_HOST="${SOLR_EXTRACT_SERVER_HOST:-$SOLR_SERVER_HOST}"
SOLR_EXTRACT_SERVER_PORT="${SOLR_EXTRACT_SERVER_PORT:-$SOLR_SERVER_PORT}"
SOLR_EXTRACT_CONTEXT="${SOLR_EXTRACT_CONTEXT:-$SOLR_CONTEXT}"

#
# Write solr-config to application's config.ini
#
echo "Writing Solr connection paramters to configuration ..."
CONFIG_INI="$BASEDIR/application/configs/$OPUS_CONF"
"$SCRIPT_PATH/install-config-solr.sh" "$CONFIG_INI" \
    "${SOLR_SERVER_HOST}" "$SOLR_SERVER_PORT" "${SOLR_CONTEXT}" \
    "${SOLR_EXTRACT_SERVER_HOST}" "$SOLR_EXTRACT_SERVER_PORT" "${SOLR_EXTRACT_CONTEXT}"

#
# Import some test documents optionally
#

[ -z "$IMPORT_TESTDATA" ] && read -p "Import test data? [Y]: " IMPORT_TESTDATA
if [ -z "$IMPORT_TESTDATA" ] || [ "$IMPORT_TESTDATA" = Y ] || [ "$IMPORT_TESTDATA" = y ] ;
then
    echo "Installing test data ..."
    # TODO call script for installing testdata
fi

#
# Set file permissions
#
# TODO what user should be used
#

# change file owner of all files in $BASEDIR to $OPUS_USER_NAME
# TODO chown -R "$OWNER" "$BASEDIR"

# set permission in workspace directory appropriately
# TODO cd "$(readlink "$BASEDIR/workspace")"
# TODO find ../workspace -type d -print0 | xargs -0 -- chmod 777
# TODO find ../workspace -type f -print0 | xargs -0 -- chmod 666

#
# Restart Apache2 (optionally)
#
# - requires script to run with 'sudo'
#

[[ -z "$RESTART_APACHE" ]] && read -p "Restart Apache2 [Y]? " RESTART_APACHE

if [[ -z "$RESTART_APACHE" || "$RESTART_APACHE" = Y ]] ;
then
  service apache2 restart
fi

echo
echo
echo "OPUS 4 is running now! Point your browser to"
echo "http://localhost$OPUS_URL_BASE"
echo
