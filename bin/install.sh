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
# TODO add more explanatory output
# TODO fix solr setup
# TODO remove code that requires "sudo"? Keep it separate?
#
# Parameters:
# -a <filename> : Sets name for Apache configuration file
# -c <filename> : Sets name for OPUS configuration file (used for testing)
#

set -e

# Parse command line options

while getopts ":a:c:" opt; do
  case $opt in
    a) APACHE_CONF="$OPTARG"
    ;;
    c) OPUS_CONF="$OPTARG"
    ;;
  esac
done

# Check for sudo

if [[ $EUID -eq 0 ]]; then
    SUDO_ENABLED=1
else
    SUDO_ENABLED=0
fi

# Set defaults
APACHE_CONF="${APACHE_CONF:-apache.conf}"
OPUS_CONF="${OPUS_CONF:-config.ini}"
OPUS_CONSOLE_CONF="${OPUS_CONSOLE_CONF:-console.ini}"

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
# Prepare workspace
#

"$SCRIPT_PATH/prepare-workspace.sh"

#
# Install Composer and dependencies
#
# Composer is not executed as root, but as the current user.
#

echo
echo "Installing Composer and dependencies ..."
echo

if [[ $SUDO_ENABLED -eq 0 ]] ;
then
    "$SCRIPT_PATH/install-composer.sh" install
else
    sudo -u "$SUDO_USER" "$SCRIPT_PATH/install-composer.sh" install
fi

#
# Prepare Apache2 configuration
#

echo
echo "Creating Apache2 site configuration ..."
echo

"$SCRIPT_PATH/install-apache.sh" "$OPUS_URL_BASE" "apache24.conf.template" "$APACHE_CONF" "$OS" 'N'

#
# Setup database
#

"$SCRIPT_PATH/install-database.sh"

#
# Set file permissions
#

cd "$BASEDIR"

if [[ $SUDO_ENABLED -eq 1 ]] ;
then
    "$SCRIPT_PATH/set-file-permissions.sh" -g www-data
else
    cat <<LimitString
Make sure read/write permissions for workspace folder are setup properly. You can use to set default permissions:

sudo bin/set-file-permissions.sh
LimitString
fi

#
# Set password for administrator account
#

while [[ -z $ADMIN_PWD || "$ADMIN_PWD" != "$ADMIN_PWD_VERIFY" ]] ;
do
  echo
  read -p "Please enter password for OPUS 'admin' account: " -s ADMIN_PWD
  echo
  read -p "Please enter password again: " -s ADMIN_PWD_VERIFY
  echo
  if [[ $ADMIN_PWD != $ADMIN_PWD_VERIFY ]] ;
  then
    echo "Passwords do not match. Please try again."
  fi
done

php "$BASEDIR/scripts/change-password.php" admin "$ADMIN_PWD"

#
# Configure Solr connection
#
# Add Solr connection parameters to configuration files.
#

echo
echo "Solr configuration"
echo

# ask for desired port of solr service
[ -z "$SOLR_SERVER_PORT" ] && read -p "Solr server port number [8983]: " SOLR_SERVER_PORT
SOLR_SERVER_PORT="${SOLR_SERVER_PORT:-8983}"

cd "$BASEDIR"

# ask for host of solr service
[ -z "$SOLR_SERVER_HOST" ] && read -p "Solr server host name [localhost]: " SOLR_SERVER_HOST
SOLR_SERVER_HOST="${SOLR_SERVER_HOST:-localhost}"

[ -z "$SOLR_CONTEXT" ] && read -p "Solr context name [/solr/opus4]: " SOLR_CONTEXT
SOLR_CONTEXT="${SOLR_CONTEXT:-/solr/opus4}"

# Text extraction can use a different Solr connection
[ -z "$SOLR_EXTRACT" ] && read -p "Use different connection for text extraction? [N]: " SOLR_EXTRACT
SOLR_EXTRACT="${SOLR_EXTRACT:-N}"

if [ "$SOLR_EXTRACT" = Y ] || [ "$SOLR_EXTRACT" = y ] ;
then
  [ -z "$SOLR_EXTRACT_SERVER_HOST" ] && read -p "Solr extraction server host [$SOLR_SERVER_HOST]: " SOLR_EXTRACT_SERVER_HOST

  [ -z "$SOLR_EXTRACT_SERVER_PORT" ] && read -p "Solr extraction server port [$SOLR_SERVER_PORT]: " SOLR_EXTRACT_SERVER_PORT

  [ -z "$SOLR_EXTRACT_CONTEXT" ] && read -p "Solr extraction server context [$SOLR_CONTEXT]: " SOLR_EXTRACT_CONTEXT
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

    echo "Creating test configuration ..."

    cd "$BASEDIR/tests"
    cp config.ini.template config.ini

    echo "done"

    echo "Installing test data ..."
    "$SCRIPT_PATH/install-testdata.sh"
    echo "done"

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

    PING_URL="http://${SOLR_SERVER_HOST}:${SOLR_SERVER_PORT}${SOLR_CONTEXT}/admin/ping"

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
    "$BASEDIR/bin/opus4" index:index
fi

cd "$BASEDIR"

#
# Restart Apache2 (optionally)
#
# - requires script to run with 'sudo'
#

if [[ "$SUDO_ENABLED" -eq 1 ]] ;
then
    [[ -z "$RESTART_APACHE" ]] && read -p "Restart Apache2 [Y]? " RESTART_APACHE

    if [[ -z "$RESTART_APACHE" || "$RESTART_APACHE" = Y ]] ;
    then
      service apache2 restart
    fi
else
    echo -e "You need to restart Apache2, e.g. run 'service apache2 restart'."
fi

echo
echo "OPUS 4 is running now! Point your browser to"
echo "http://localhost$OPUS_URL_BASE"
echo
