#!/bin/bash
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
# @copyright   Copyright (c) 2010, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id$

#set -ex
set -e

BASEDIR=/var/local/opus4

MYSQL_CLIENT=/usr/bin/mysql

cd $BASEDIR

OPUS4_DB_NAME=`grep '^dbname=' $BASEDIR/opus4/db/createdb.sh | cut -d= -f2 | sed -e "s/'//g"`
OPUS4_DB_ADMIN=`grep '^user=' $BASEDIR/opus4/db/createdb.sh | cut -d= -f2 | sed -e "s/'//g"`
OPUS4_DB_USER=`grep 'db.params.username' $BASEDIR/opus4/application/configs/config.ini | cut -d' ' -f3 | sed -e "s/'//g"`
MYSQL_COMMANDS=''

read -p "MySQL Root User [root]: " MYSQLROOT
read -p "MySQL DBMS Host [leave blank for using Unix domain sockets]: " MYSQLHOST
read -p "MySQL DBMS Port [leave blank for using Unix domain sockets]: " MYSQLPORT
echo ""
if [ -z "$MYSQLROOT" ]; then
  MYSQLROOT=root
fi
if [ -z "$MYSQLHOST" ]; then
  HOST=localhost
else
  HOST="$MYSQLHOST"
fi

read -p "Delete OPUS4 Database $OPUS4_DB_NAME [Y]: " DELETE_DATABASE
if [ -z "$DELETE_DATABASE" ] || [ "$DELETE_DATABASE" = "Y" ] || [ "$DELETE_OPUS4_DB_USER" = "y" ]
then
  MYSQL_COMMANDS="$MYSQL_COMMANDS DROP DATABASE IF EXISTS $OPUS4_DB_NAME ;"   
fi

read -p "Delete OPUS4 Database User $OPUS4_DB_USER [Y]: " DELETE_OPUS4_DB_USER
if [ -z "$DELETE_OPUS4_DB_USER" ] || [ "$DELETE_OPUS4_DB_USER" = "Y" ] || [ "$DELETE_OPUS4_DB_USER" = "y" ]
then
  MYSQL_COMMANDS="$MYSQL_COMMANDS DROP USER '$OPUS4_DB_USER'@'$HOST' ;"
fi

read -p "Delete OPUS4 Database Admin User $OPUS4_DB_ADMIN [Y]: " DELETE_OPUS4_DB_ADMIN
if [ -z "$DELETE_OPUS4_DB_ADMIN" ] || [ "$DELETE_OPUS4_DB_ADMIN" = "Y" ] || [ "$DELETE_OPUS4_DB_ADMIN" = "y" ]
then
  MYSQL_COMMANDS="$MYSQL_COMMANDS DROP USER '$OPUS4_DB_ADMIN'@'$HOST' ;"
fi

if [ -n "$MYSQL_COMMANDS" ]
then
  MYSQL="$MYSQL_CLIENT --default-character-set=utf8 -u $MYSQLROOT -p -v"
  if [ -n "$MYSQLHOST" ]; then
    MYSQL="$MYSQL -h $MYSQLHOST"
  fi
  if [ -n "$MYSQLPORT" ]; then
    MYSQL="$MYSQL -P $MYSQLPORT"
  fi

  echo ""
  echo "Next you'll be now prompted to enter the root password of your MySQL server"
  $MYSQL -e "$MYSQL_COMMANDS"
fi

/etc/init.d/opus4-solr-jetty stop
update-rc.d -f opus4-solr-jetty remove
rm -rf /etc/init.d/opus4-solr-jetty
rm -rf /etc/default/jetty 

OPUS4_USER_ACCOUNT=`grep '^JETTY_USER=' $BASEDIR/install/opus4-solr-jetty.conf | cut -d= -f2`

read -p "Remove OPUS4 instance directory? [N]: " REMOVE_INSTANCE_DIR
if [ "$REMOVE_INSTANCE_DIR" = "Y" ] || [ "$REMOVE_INSTANCE_DIR" = "y" ]
then
  cd $BASEDIR/..
  rm -rf $BASEDIR
fi

if [ -n "$OPUS4_USER_ACCOUNT" ]
then
  read -p "Remove OPUS4 system account $OPUS4_USER_ACCOUNT [Y]: " $DELETE_OPUS4_USER_ACCOUNT
  if [ -z "$DELETE_OPUS4_USER_ACCOUNT" ] || [ "$DELETE_OPUS4_USER_ACCOUNT" = "Y" ] || [ "$DELETE_OPUS4_USER_ACCOUNT" = "y" ]
  then
    userdel -f $OPUS4_USER_ACCOUNT
  fi
fi

echo "Deinstallation of OPUS4 completed."
