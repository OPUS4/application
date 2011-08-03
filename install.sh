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
# @copyright   Copyright (c) 2010-2011, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id$

set -e

# START USER-CONFIGURATION

BASEDIR='/var/local/opus4'

# END OF USER-CONFIGURATION

SCRIPT_NAME="`basename "$0"`"
SCRIPT_NAME_FULL="`readlink -f "$0"`"
SCRIPT_PATH="`dirname "$SCRIPT_NAME_FULL"`"

# check input parameter
if [ $# -lt 1 ]
then
  echo "Missing Argument: use $SCRIPT_NAME {ubuntu,suse}"
  echo "Installation aborted."
  exit 1
fi

if [ "$1" = ubuntu ] || [ "$1" = suse ]
then
  OS="$1"
else
  echo "Invalid Argument $1 : use $SCRIPT_NAME {ubuntu,suse}"
  echo "Installation aborted."
  exit 1
fi

MYSQL_CLIENT='/usr/bin/mysql'

# including installer components
if [ -f "$SCRIPT_PATH/installer.includes" ]; then
    . "$SCRIPT_PATH/installer.includes"
fi

cd "$BASEDIR"

# download required files into download folder
if [ ! -d downloads ]
then
  "$SCRIPT_PATH/install-download-files.sh" "$BASEDIR/downloads"
fi

# install required libraries into libraries folder
cd libs

tar xfvz "$BASEDIR/downloads/zend.tar.gz"
ln -svf ZendFramework-1.10.6-minimal ZendFramework

mkdir -p jpgraph-3.0.7
tar xfvz "$BASEDIR/downloads/jpgraph.tar.gz" --directory jpgraph-3.0.7/
ln -svf jpgraph-3.0.7 jpgraph

cp -r "$BASEDIR/downloads/SolrPhpClient_r36" .
ln -svf "SolrPhpClient_r36" SolrPhpClient

cp "$BASEDIR/downloads/jquery.js" "$BASEDIR/opus4/public/js"

cd "$BASEDIR"

# create .htaccess
[[ -z $OPUS_URL_BASE ]] && OPUS_URL_BASE='/opus4'
sed -e "s!<template>!$OPUS_URL_BASE!" opus4/public/htaccess-template > opus4/public/.htaccess
if [ "$OS" = ubuntu ]
then
  cp opus4/public/.htaccess opus4/public/.htaccess.tmp
  sed -e 's!#Enable for UBUNTU/DEBIAN:# !!' opus4/public/.htaccess.tmp > opus4/public/.htaccess
  rm opus4/public/.htaccess.tmp
fi

# prepare apache config
sed -e "s!/OPUS_URL_BASE!/$OPUS_URL_BASE!g; s!/BASEDIR/!/$BASEDIR/!; s!//*!/!g" "$BASEDIR/apacheconf/apache.conf.template" > "$BASEDIR/apacheconf/opus4"

# promt for username, if required
echo "OPUS requires a dedicated system account under which Solr will be running."
echo "In order to create this account, you will be prompted for some information."
[[ -z $OPUS_USER_NAME ]] && read -p "System Account Name [opus4]: " OPUS_USER_NAME
if [ -z "$OPUS_USER_NAME" ]; then
  OPUS_USER_NAME='opus4'
fi
OPUS_USER_NAME_ESC=`echo "$OPUS_USER_NAME" | sed 's/\!/\\\!/g'`

# create user account
[[ -z $CREATE_OPUS_USER ]] && CREATE_OPUS_USER=Y
if [ "$CREATE_OPUS_USER" = Y ];
then
  if [ "$OS" = ubuntu ]
  then
    useradd -c 'OPUS 4 Solr manager' --system "$OPUS_USER_NAME_ESC"
  else
    useradd -c 'OPUS 4 Solr manager' --system --create-home --shell /bin/bash "$OPUS_USER_NAME_ESC"
  fi
fi

# preparing OWNER string for chown-calls.
OPUS_GROUP_NAME="`id -gn "$OPUS_USER_NAME"`"
OWNER="$OPUS_USER_NAME:$OPUS_GROUP_NAME"

# prompt for database parameters
[[ -z $DBNAME               ]] && read -p "New OPUS Database Name [opus400]: "          DBNAME
[[ -z $ADMIN                ]] && read -p "New OPUS Database Admin Name [opus4admin]: " ADMIN
[[ -z $ADMIN_PASSWORD       ]] && read -p "New OPUS Database Admin Password: " -s       ADMIN_PASSWORD
echo
[[ -z $WEBAPP_USER          ]] && read -p "New OPUS Database User Name [opus4]: "       WEBAPP_USER
[[ -z $WEBAPP_USER_PASSWORD ]] && read -p "New OPUS Database User Password: " -s        WEBAPP_USER_PASSWORD
echo
[[ -z $MYSQLHOST            ]] && read -p "MySQL DBMS Host [leave blank for using Unix domain sockets]: " MYSQLHOST
[[ -z $MYSQLPORT            ]] && read -p "MySQL DBMS Port [leave blank for using Unix domain sockets]: " MYSQLPORT
echo
[[ -z $MYSQLROOT            ]] && read -p "MySQL Root User [root]: "                                      MYSQLROOT
echo


# set defaults if value is not given
if [ -z "$DBNAME" ]; then
   DBNAME='opus400'
fi
if [ -z "$ADMIN" ]; then
   ADMIN='opus4admin'
fi
if [ -z "$WEBAPP_USER" ]; then
   WEBAPP_USER='opus4'
fi
if [ -z "$MYSQLROOT" ]; then
   MYSQLROOT='root'
fi
if [ -z "$MYSQLHOST" ]; then
  HOST='localhost'
else
  HOST="$MYSQLHOST"
fi

# escape ! (for later use in sed substitute)
MYSQLHOST_ESC=`echo "$MYSQLHOST" | sed 's/\!/\\\!/g'`
MYSQLPORT_ESC=`echo "$MYSQLPORT" | sed 's/\!/\\\!/g'`
WEBAPP_USER_ESC=`echo "$WEBAPP_USER" | sed 's/\!/\\\!/g'`
WEBAPP_USER_PASSWORD_ESC=`echo "$WEBAPP_USER_PASSWORD" | sed 's/\!/\\\!/g'`
DBNAME_ESC=`echo "$DBNAME" | sed 's/\!/\\\!/g'`
ADMIN_ESC=`echo "$ADMIN" | sed 's/\!/\\\!/g'`
ADMIN_PASSWORD_ESC=`echo "$ADMIN_PASSWORD" | sed 's/\!/\\\!/g'`
ADMIN_PASSWORD_QUOTED=`echo "$(printf %q "$ADMIN_PASSWORD")"`

# process creating mysql user and database
MYSQL="$MYSQL_CLIENT --default-character-set=utf8 -u $MYSQLROOT -p -v"
MYSQL_OPUS4ADMIN="$MYSQL_CLIENT --default-character-set=utf8 -u $ADMIN -p$ADMIN_PASSWORD_QUOTED -v"
if [ -n "$MYSQLHOST" ]
then
  MYSQL="$MYSQL -h $MYSQLHOST"
  MYSQL_OPUS4ADMIN="$MYSQL_OPUS4ADMIN -h $MYSQLHOST"
fi
if [ -n "$MYSQLPORT" ]
then
  MYSQL="$MYSQL -P $MYSQLPORT"
  MYSQL_OPUS4ADMIN="$MYSQL_OPUS4ADMIN -P $MYSQLPORT"
fi

echo "Next you'll be now prompted to enter the root password of your MySQL server"
$MYSQL <<LimitString
CREATE DATABASE $DBNAME DEFAULT CHARACTER SET = UTF8 DEFAULT COLLATE = UTF8_GENERAL_CI;
GRANT ALL PRIVILEGES ON $DBNAME.* TO '$ADMIN'@'$HOST' IDENTIFIED BY '$ADMIN_PASSWORD';
GRANT SELECT,INSERT,UPDATE,DELETE ON $DBNAME.* TO '$WEBAPP_USER'@'$HOST' IDENTIFIED BY '$WEBAPP_USER_PASSWORD';
FLUSH PRIVILEGES;
LimitString

# create config.ini and set database related parameters
cd "$BASEDIR/opus4/application/configs"
sed -e "s!^db.params.host =!db.params.host = '$MYSQLHOST_ESC'!" \
    -e "s!^db.params.port =!db.params.port = '$MYSQLPORT_ESC'!" \
    -e "s!^db.params.username =!db.params.username = '$WEBAPP_USER_ESC'!" \
    -e "s!^db.params.password =!db.params.password = '$WEBAPP_USER_PASSWORD_ESC'!" \
    -e "s!^db.params.dbname =!db.params.dbname = '$DBNAME_ESC'!" config.ini.template > config.ini

# create createdb.sh and set database related parameters
cd "$BASEDIR/opus4/db"
sed -e "s!^user=!user='$ADMIN_ESC'!" \
    -e "s!^password=!password='$ADMIN_PASSWORD_ESC'!" \
    -e "s!^host=!host='$MYSQLHOST_ESC'!" \
    -e "s!^port=!port='$MYSQLPORT_ESC'!" \
    -e "s!^dbname=!dbname='$DBNAME_ESC'!" createdb.sh.template > createdb.sh
chmod +x createdb.sh
./createdb.sh

# install and configure Solr search server
cd "$BASEDIR"
[[ -z $INSTALL_SOLR ]] && read -p "Install and configure Solr server? [Y]: " INSTALL_SOLR
if [ -z "$INSTALL_SOLR" ] || [ "$INSTALL_SOLR" = Y ] || [ "$INSTALL_SOLR" = y ]
then
  tar xfvz "$BASEDIR/downloads/solr.tgz"
  ln -sf apache-solr-1.4.1 solr
  cd solr
  cp -r example opus4
  cd opus4
  rm -rf example-DIH exampledocs multicore/exampledocs
  cd solr/conf
  ln -sf "$BASEDIR/solrconfig/schema.xml"
  ln -sf "$BASEDIR/solrconfig/solrconfig.xml"
  cd ../../
  ln -sf "$BASEDIR/solrconfig/logging.properties"

  [[ -z $SOLR_SERVER_PORT ]] && read -p "Solr server port number [8983]: " SOLR_SERVER_PORT
  if [ -z "$SOLR_SERVER_PORT" ]; then
    SOLR_SERVER_PORT='8983';
  fi
  SOLR_SERVER_PORT_ESC=`echo "$SOLR_SERVER_PORT" |sed 's/\!/\\\!/g'`

  cd "$BASEDIR/opus4/application/configs"
  cp config.ini config.ini.tmp
  sed -e "s!^searchengine.index.host =!searchengine.index.host = 'localhost'!" \
      -e "s!^searchengine.index.port =!searchengine.index.port = '$SOLR_SERVER_PORT_ESC'!" \
      -e "s!^searchengine.index.app =!searchengine.index.app = 'solr'!" \
      -e "s!^searchengine.extract.host =!searchengine.extract.host = 'localhost'!" \
      -e "s!^searchengine.extract.port =!searchengine.extract.port = '$SOLR_SERVER_PORT_ESC'!" \
      -e "s!^searchengine.extract.app =!searchengine.extract.app = 'solr'!" config.ini.tmp > config.ini 
  rm config.ini.tmp

  cd "$BASEDIR/install"
  if [ "$OS" = suse ]
  then
    cp opus4-solr-jetty opus4-solr-jetty.tmp
    sed -e "s!^START_STOP_DAEMON=1!START_STOP_DAEMON=0!" opus4-solr-jetty.tmp > opus4-solr-jetty
    rm opus4-solr-jetty.tmp
  fi
  sed -e "s!^JETTY_PORT=!JETTY_PORT=$SOLR_SERVER_PORT_ESC!" \
      -e "s!^JETTY_USER=!JETTY_USER=$OPUS_USER_NAME_ESC!" opus4-solr-jetty.conf.template > opus4-solr-jetty.conf
  chmod +x opus4-solr-jetty

  [[ -z $INSTALL_INIT_SCRIPT ]] && read -p "Install init.d script to start and stop Solr server automatically? [Y]: " INSTALL_INIT_SCRIPT
  if [ -z "$INSTALL_INIT_SCRIPT" ] || [ "$INSTALL_INIT_SCRIPT" = Y ] || [ "$INSTALL_INIT_SCRIPT" = y ]
  then
    ln -sf "$BASEDIR/install/opus4-solr-jetty" /etc/init.d/opus4-solr-jetty
    ln -sf "$BASEDIR/install/opus4-solr-jetty.conf" /etc/default/jetty
    ln -sf "$BASEDIR/install/jetty-logging.xml" "$BASEDIR/solr/opus4/etc/jetty-logging.xml"
    chmod +x /etc/init.d/opus4-solr-jetty
    if [ "$OS" = ubuntu ]
    then
      update-rc.d -f opus4-solr-jetty remove
      update-rc.d opus4-solr-jetty defaults
    else
      chkconfig --del opus4-solr-jetty
      chkconfig --set opus4-solr-jetty on
    fi
  fi

  # change file owner of solr installation
  chown -R "$OWNER" "$BASEDIR/apache-solr-1.4.1"
  chown -R "$OWNER" "$BASEDIR/solrconfig"

  # start Solr server
  ./opus4-solr-jetty start
fi

# import some test documents
[[ -z $IMPORT_TESTDATA ]] && read -p "Import test data? [Y]: " IMPORT_TESTDATA
if [ -z "$IMPORT_TESTDATA" ] || [ "$IMPORT_TESTDATA" = Y ] || [ "$IMPORT_TESTDATA" = y ]
then
  # import test data
  cd "$BASEDIR"
  for i in `find testdata/sql -name *.sql \( -type f -o -type l \) | sort`; do
    echo "Inserting file '${i}'"
    eval "$MYSQL_OPUS4ADMIN" "$DBNAME" < "${i}"
  done

  # copy test fulltexts to workspace directory
  cp -rv testdata/fulltexts/* workspace/files

  # sleep some seconds to ensure the server is running
  echo -en "\n\nwait until Solr server is running..."

  while :; do
    echo -n "."
    wget -q -O /dev/null "http://localhost:$SOLR_SERVER_PORT/solr/admin/ping" && break
    sleep 2
  done

  echo "completed."
  echo -e "Solr server is running under http://localhost:$SOLR_SERVER_PORT/solr\n"

  # start indexing of testdata
  php5 "$BASEDIR/opus4/scripts/SolrIndexBuilder.php"
fi

# change file owner of all files in $BASEDIR to $OPUS_USER_NAME
chown -R "$OWNER" "$BASEDIR"

# set permission in workspace directory appropriately
cd "$BASEDIR"
chmod -R 777 workspace

# delete tar archives
[[ -z $DELETE_DOWNLOADS ]] && read -p "Delete downloads? [N]: " DELETE_DOWNLOADS
if [ "$DELETE_DOWNLOADS" = Y ] || [ "$DELETE_DOWNLOADS" = y ]; then
  rm -rf downloads
fi

[[ -z $RESTART_APACHE ]] && RESTART_APACHE=Y
if [ "$RESTART_APACHE" = Y ];
then
  echo 'restart apache webserver ...'
  /etc/init.d/apache2 restart
fi
  
echo
echo
echo "OPUS 4 is running now! Point your browser to http://localhost/$OPUS_URL_BASE"
echo
