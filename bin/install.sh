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

SOLR_SERVER_URL='http://archive.apache.org/dist/lucene/solr/5.2.1/solr-5.2.1.tgz'

MYSQL_CLIENT='/usr/bin/mysql'

# END OF USER-CONFIGURATION

SCRIPT_NAME="`basename "$0"`"
SCRIPT_NAME_FULL="`readlink -f "$0"`"
SCRIPT_PATH="`dirname "$SCRIPT_NAME_FULL"`"
BASEDIR="`dirname "$SCRIPT_PATH"`"

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

cd "$BASEDIR"

# Install dependencies
# --------------------

"$SCRIPT_PATH/install-composer.sh" "$BASEDIR"

# Link to framework
# -----------------

cd "$BASEDIR/db"
ln -svnf "$BASEDIR/vendor/opus4-repo/framework/db/schema" "schema"
cd "$BASEDIR"

exit 0;

# Create .htaccess file
# ---------------------

[[ -z $OPUS_URL_BASE ]] && read -p "Base URL for OPUS [opus4]: " OPUS_URL_BASE

OPUS_URL_BASE="/${OPUS_URL_BASE:-opus4}"
OPUS_URL_BASE_ESC=`echo "$OPUS_URL_BASE" | sed 's/\!/\\\!/g'`

sed -e "s!<template>!$OPUS_URL_BASE_ESC!" public/htaccess-template > public/.htaccess
if [ "$OS" = ubuntu ]
then
  sed -i -e 's!#Enable for UBUNTU/DEBIAN:# !!' public/.htaccess
fi

# Prepare apache configuration
# ----------------------------

sed -e "s!/OPUS_URL_BASE!/$OPUS_URL_BASE!g; s!/BASEDIR/!/$BASEDIR/!; s!//*!/!g" "$BASEDIR/apacheconf/apache.conf.template" > "$BASEDIR/apacheconf/apache.conf"

# Create OPUS user account
# ------------------------

# promt for username, if required
echo "OPUS requires a dedicated system account under which Solr will be running."
echo "In order to create this account, you will be prompted for some information."

while [ -z "$OPUS_USER_NAME" ]; do
	[[ -z $OPUS_USER_NAME ]] && read -p "System Account Name [opus4]: " OPUS_USER_NAME
	if [ -z "$OPUS_USER_NAME" ]; then
	  OPUS_USER_NAME='opus4'
	fi
	OPUS_USER_NAME_ESC=`echo "$OPUS_USER_NAME" | sed 's/\!/\\\!/g'`

	if getent passwd "$OPUS_USER_NAME" &>/dev/null; then
		echo "Selected user account exists already."
		read -p "Use it anyway? [N] " choice
		case "${choice,,}" in
			"y"|"yes"|"j"|"ja")
				CREATE_OPUS_USER=N
				;;
			*)
				OPUS_USER_NAME=
		esac
	fi
done

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

# Create database
# ---------------

# prompt for database parameters
[[ -z $DBNAME               ]] && read -p "New OPUS Database Name [opus400]: "          DBNAME
[[ -z $ADMIN                ]] && read -p "New OPUS Database Admin Name [opus4admin]: " ADMIN
[[ -z $ADMIN_PASSWORD       ]] && read -p "New OPUS Database Admin Password: " -s       ADMIN_PASSWORD
echo
[[ -z $WEBAPP_USER          ]] && read -p "New OPUS Database User Name [opus4]: "       WEBAPP_USER
[[ -z $WEBAPP_USER_PASSWORD ]] && read -p "New OPUS Database User Password: " -s        WEBAPP_USER_PASSWORD
echo
[[ -z $MYSQLHOST            ]] && read -p "MySQL DBMS Host [localhost]: "               MYSQLHOST
[[ -z $MYSQLPORT            ]] && read -p "MySQL DBMS Port [3306]: "                    MYSQLPORT
[[ -z $MYSQLROOT            ]] && read -p "MySQL Root User [root]: "                    MYSQLROOT
read -p "MySQL Root User Password: " -s MYSQLROOT_PASSWORD

echo

# set defaults if value is not given
DBNAME="${DBNAME:-opus400}"
ADMIN="${ADMIN:-opus4admin}"
WEBAPP_USER="${WEBAPP_USER:-opus4}"
MYSQLROOT="${MYSQLROOT:-root}"
MYSQLHOST="${MYSQLHOST:-localhost}"
MYSQLPORT="${MYSQLPORT:-3306}"

# escape ! (for later use in sed substitute)
MYSQLHOST_ESC="${MYSQLHOST//\!/\\\!}"
MYSQLPORT_ESC="${MYSQLPORT//\!/\\\!}"
WEBAPP_USER_ESC="${WEBAPP_USER//\!/\\\!}"
WEBAPP_USER_PASSWORD_ESC="${WEBAPP_USER_PASSWORD//\!/\\\!}"
DBNAME_ESC="${DBNAME//\!/\\\!}"
ADMIN_ESC="${ADMIN//\!/\\\!}"
ADMIN_PASSWORD_ESC="${ADMIN_PASSWORD//\!/\\\!}"

# prepare to access MySQL service
MYSQL_OPTS=""
[ "localhost" != "$MYSQLHOST" ] && MYSQL_OPTS="-h $MYSQLHOST"
[ "3306" != "$MYSQLPORT" ] && MYSQL_OPTS="$MYSQL_OPTS -P $MYSQLPORT"

mysqlRoot() {
  "$MYSQL_CLIENT" --defaults-file=<(echo -e "[client]\npassword=${MYSQLROOT_PASSWORD}") --default-character-set=utf8 ${MYSQL_OPTS} -u "$MYSQLROOT" -v
}

mysqlOpus4Admin() {
  "$MYSQL_CLIENT" --defaults-file=<(echo -e "[client]\npassword=${ADMIN_PASSWORD}") --default-character-set=utf8 ${MYSQL_OPTS} -u "$ADMIN" -v $1
}

# process creating mysql user and database
mysqlRoot <<LimitString
CREATE DATABASE IF NOT EXISTS $DBNAME DEFAULT CHARACTER SET = UTF8 DEFAULT COLLATE = UTF8_GENERAL_CI;
GRANT ALL PRIVILEGES ON $DBNAME.* TO '$ADMIN'@'$MYSQLHOST' IDENTIFIED BY '$ADMIN_PASSWORD';
GRANT SELECT,INSERT,UPDATE,DELETE ON $DBNAME.* TO '$WEBAPP_USER'@'$MYSQLHOST' IDENTIFIED BY '$WEBAPP_USER_PASSWORD';
FLUSH PRIVILEGES;
LimitString

# Update configuration files
# --------------------------

# create config.ini and set database related parameters
cd "$BASEDIR/application/configs"
cp config.ini.template config.ini
if [ localhost != "$MYSQLHOST" ]; then
  sed -i -e "s!^; db.params.host = localhost!db.params.host = '$MYSQLHOST_ESC'!" config.ini
fi
if [ 3306 != "$MYSQLPORT" ]; then
  sed -i -e "s!^; db.params.port = 3306!db.params.port = '$MYSQLPORT_ESC'!" config.ini
fi
sed -i -e "s!@db.user.name@!'$WEBAPP_USER_ESC'!" \
       -e "s!@db.user.password@!'$WEBAPP_USER_PASSWORD_ESC'!" \
       -e "s!@db.name@!'$DBNAME_ESC'!" config.ini

# create createdb.sh and set database related parameters
# TODO creating symbolic links
cd "$BASEDIR/db"
if [ ! -e createdb.sh ]; then
  cp createdb.sh.template createdb.sh
  if [ localhost != "$MYSQLHOST" ]; then
    sed -i -e "s!^# host=localhost!host='$MYSQLHOST_ESC'!" createdb.sh
  fi
  if [ 3306 != "$MYSQLPORT" ]; then
    sed -i -e "s!^# port=3306!port='$MYSQLPORT_ESC'!" createdb.sh
  fi
  sed -i -e "s!@db.admin.name@!'$ADMIN_ESC'!" \
         -e "s!@db.admin.password@!'$ADMIN_PASSWORD_ESC'!" \
         -e "s!@db.name@!'$DBNAME_ESC'!" createdb.sh

  bash createdb.sh || rm createdb.sh
fi

# Install and configure Solr search server
# ----------------------------------------

cd "$BASEDIR"

[ -z "$INSTALL_SOLR" ] && read -p "Install and configure Solr server? [Y]: " INSTALL_SOLR
if [ -z "$INSTALL_SOLR" ] || [ "$INSTALL_SOLR" = Y ] || [ "$INSTALL_SOLR" = y ]
then

  # extract archive name from URL
  SOLR_ARCHIVE_NAME="${SOLR_SERVER_URL##*/}"
  SOLR_ARCHIVE_NAME="${SOLR_ARCHIVE_NAME%%\?*}"

  # fetch installation archive if missing locally
  mkdir -p "downloads"
  if [ ! -f "downloads/$SOLR_ARCHIVE_NAME" ]; then
    wget -O "downloads/$SOLR_ARCHIVE_NAME" "$SOLR_SERVER_URL"
    if [ $? -ne 0 -o ! -f "downloads/$SOLR_ARCHIVE_NAME" ]
    then
      echo "Unable to download Solr service from $SOLR_SERVER_URL"
      exit 1
    fi
  fi

  # ask for desired port of solr service
  [ -z "$SOLR_SERVER_PORT" ] && read -p "Solr server port number [8983]: " SOLR_SERVER_PORT
  SOLR_SERVER_PORT="${SOLR_SERVER_PORT:-8983}"

  # extract name of folder contained in archive
  SOLR_DIR="$(tar tzf "downloads/$SOLR_ARCHIVE_NAME" | head -1)"
  SOLR_DIR="${SOLR_DIR%%/*}"

  SOLR_VERSION="${SOLR_DIR#solr-}"
  SOLR_MAJOR="${SOLR_VERSION%%.*}"

  # stop any running solr service
  lsof -i ":$SOLR_SERVER_PORT" &>/dev/null && {
    (
      echo "stopping running Solr service ..."

      if [ -x /etc/init.d/opus4-solr-jetty ]; then
        /etc/init.d/opus4-solr-jetty stop
      elif [ -x /etc/init.d/solr ]; then
        /etc/init.d/solr stop
      else
        false
      fi
    ) || \
    sudo kill "$(lsof -i ":$SOLR_SERVER_PORT" | awk 'NR>1 {print $2}')" || \
    {
      cat >&2 <<EOT
stopping running Solr service failed, please stop any service listening on
port $SOLR_SERVER_PORT ...
EOT
      exit 1
    }
  }

  # extract archive into basedir (expecting to create folder named solr-x.y.z)
  echo "extracting Solr archive ..."
  tar xfz "downloads/$SOLR_ARCHIVE_NAME"

  cd "$SOLR_DIR"

  SOLR_BASE_DIR="$(pwd)"
  SOLR_CORE_DIR="$(pwd)/opus4"

  # create space for configuring solr service
  mkdir -p "${SOLR_CORE_DIR}/data/solr/conf"

  copyConfigFile() {
    NAME="${1}"
    SRC="${2}"
    DST="${3}"

    [ -e "${DST}/${NAME}" ] || {
      PRE="${NAME%.*}"
      POST="${NAME##*.}"

      VERSION="${SOLR_VERSION}"

      SRCNAME=
      while [ -z "${SRCNAME}" -o \( ! -e "${SRCNAME}" -a -n "${VERSION}" \) ]
      do
        SRCNAME="${SRC}/${PRE}-${VERSION}.${POST}"
        VERSION="${VERSION%.*}"
      done

      if [ -e "${SRCNAME}" ]; then
        echo "setting up ${SRCNAME} as ${DST}/${NAME}"
        ln -sf "${SRCNAME}" "${DST}/${NAME}"
      else
        echo "setting up ${SRC}/${NAME} as ${DST}/${NAME}"
        ln -sf "${SRC}/${NAME}" "${DST}/${NAME}"
      fi
    }
  }

  # put configuration and schema files
  ln -sf  "$BASEDIR/solrconfig/core.properties" "${SOLR_CORE_DIR}/data/solr"

  copyConfigFile "schema.xml" "${BASEDIR}/solrconfig" "${SOLR_CORE_DIR}/data/solr/conf"
  copyConfigFile "solrconfig.xml" "${BASEDIR}/solrconfig" "${SOLR_CORE_DIR}/data/solr/conf"

  # provide logging properties
  # TODO check integration of logging.properties with recent versions of solr
  ln -sf "$BASEDIR/solrconfig/logging.properties" opus4/logging.properties

  # detect URL prefix to use
  case "$SOLR_MAJOR" in
    5)
      SOLR_CONTEXT="/solr/solr"
      ;;
    *)
      SOLR_CONTEXT="/solr"
  esac



  # write solr-config to application's config.ini
  CONFIG_INI="$BASEDIR/opus4/application/configs/config.ini"
  "$SCRIPT_PATH/install-config-solr.sh" "$CONFIG_INI" localhost "$SOLR_SERVER_PORT" "${SOLR_CONTEXT}" localhost "$SOLR_SERVER_PORT" "${SOLR_CONTEXT}"

  # change file owner of solr installation
  chown -R "$OWNER" "$BASEDIR/$SOLR_DIR"
  chown -R "$OWNER" "$BASEDIR/solrconfig"

  # install init script
  [ -z "$INSTALL_INIT_SCRIPT" ] && read -p "Install init.d script to start and stop Solr server automatically? [Y]: " INSTALL_INIT_SCRIPT
  if [ -z "$INSTALL_INIT_SCRIPT" ] || [ "$INSTALL_INIT_SCRIPT" = Y ] || [ "$INSTALL_INIT_SCRIPT" = y ]
  then
    # remove files and folders causing unneccessary errors in install script
    rm -f /etc/init.d/{opus4-solr-jetty,solr}
    rm -f "${BASEDIR}/solr"

    # run installer bundled with solr
    bin/install_solr_service.sh "../downloads/$SOLR_ARCHIVE_NAME" -d "$SOLR_CORE_DIR" -i "$BASEDIR" -p "$SOLR_SERVER_PORT" -s solr -u "$OPUS_USER_NAME"

    # make sure new service is available just like the old one
    ln -s solr /etc/init.d/opus4-solr-jetty
  else
    # (re)start solr service
    if [ -x /etc/init.d/opus4-solr-jetty ]; then
      /etc/init.d/opus4-solr-jetty restart
    elif [ -x /etc/init.d/solr ]; then
      /etc/init.d/solr restart
    fi
  fi
fi

# import test documents
# ---------------------

[ -z "$IMPORT_TESTDATA" ] && read -p "Import test data? [Y]: " IMPORT_TESTDATA
if [ -z "$IMPORT_TESTDATA" ] || [ "$IMPORT_TESTDATA" = Y ] || [ "$IMPORT_TESTDATA" = y ]
then
  # import test data
  cd "$BASEDIR"
  for i in `find opus4/tests/sql -name *.sql \( -type f -o -type l \) | sort`; do
    echo "Inserting file '${i}'"
    mysqlOpus4Admin "$DBNAME" < "${i}"
  done

  # copy test fulltexts to workspace directory
  cp -rv opus4/tests/fulltexts/* workspace/files

  # TODO is waiting for running solr required since service script has been waiting for this before
  # sleep some seconds to ensure the server is running
  echo -en "\n\nwait until Solr server is running..."

  waiting=true

  pingSolr() {
    wget -SO- "$1" 2>&1
  }

  pingSolrStatus() {
    pingSolr "$1" | sed -ne 's/^ *HTTP\/1\.[01] \([0-9]\+\) .\+$/\1/p' | head -1
  }

  case "$SOLR_MAJOR" in
    5)
      PING_URL="http://localhost:${SOLR_SERVER_PORT}${SOLR_CONTEXT}/admin/ping"
      ;;
    *)
      PING_URL="http://localhost:${SOLR_SERVER_PORT}${SOLR_CONTEXT}/admin/ping"
  esac

  while $waiting; do
    echo -n "."
    state=$(pingSolrStatus "$PING_URL")
    case $state in
      200|304)
        waiting=false
        ;;
      500)
        echo -e "\n\nSolr server responds on error:\n" >&2
        pingSolr "$PING_URL" >&2
        exit 1
        ;;
      *)
        sleep 2
    esac
  done

  echo "completed."
  echo -e "Solr server is running under http://localhost:$SOLR_SERVER_PORT/solr\n"

  # start indexing of testdata
  "$BASEDIR/opus4/scripts/SolrIndexBuilder.php"
fi

# Set file permissions
# --------------------

# change file owner of all files in $BASEDIR to $OPUS_USER_NAME
chown -R "$OWNER" "$BASEDIR"

# set permission in workspace directory appropriately
cd "$(readlink "$BASEDIR/workspace")"
find ../workspace -type d -print0 | xargs -0 -- chmod 777
find ../workspace -type f -print0 | xargs -0 -- chmod 666

# Cleanup downloads
# -----------------

# delete tar archives
[ -z "$DELETE_DOWNLOADS" ] && read -p "Delete downloads? [N]: " DELETE_DOWNLOADS
if [ "$DELETE_DOWNLOADS" = Y ] || [ "$DELETE_DOWNLOADS" = y ]; then
  rm -rf downloads
fi

# Restart Apache
# --------------

[ -z "$RESTART_APACHE" ] && RESTART_APACHE=Y
if [ "$RESTART_APACHE" = Y ];
then
  echo "restart apache webserver ..."
  /etc/init.d/apache2 restart
fi

# Installation finished
# ---------------------

echo
echo
echo "OPUS 4 is running now! Point your browser to http://localhost$OPUS_URL_BASE_ESC"
echo
