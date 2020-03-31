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
# @author      Jens Schwidder <schwidder@zib.de>
# @copyright   Copyright (c) 2010-2020, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License

#
# Downloads Apache Solr and runs the provided install script.
#
# Parameters:
# 1) Port for new Solr server
#
# Asks for port number of new Solr instance if not provides as parameter.
#

set -e

SCRIPT_NAME="`basename "$0"`"
SCRIPT_NAME_FULL="`readlink -f "$0"`"
SCRIPT_PATH="`dirname "$SCRIPT_NAME_FULL"`"

BASEDIR="`dirname "$SCRIPT_PATH"`"

if [ $# -eq 1 ] ;
then
  SOLR_SERVER_PORT="$1"
fi

# START USER-CONFIGURATION

SOLR_VERSION='7.7.2'

SOLR_SERVER_URL="http://archive.apache.org/dist/lucene/solr/$SOLR_VERSION/solr-$SOLR_VERSION.tgz"
SOLR_DIR="solr-$SOLR_VERSION"

# END OF USER-CONFIGURATION

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

# stop any running solr service
# TODO why?
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

# extract archive into basedir (expected to create folder named solr-x.y.z)
echo "Extracting Solr archive ..."
tar xfz "downloads/$SOLR_ARCHIVE_NAME"
echo "done"

#
# Run Solr installation
#

cd "$SOLR_DIR"

SOLR_BASE_DIR="$(pwd)"
SOLR_CORE_DIR="${SOLR_BASE_DIR}/opus4"

# create space for configuring solr service
mkdir -p "${SOLR_CORE_DIR}/data/solr/conf"

# put configuration and schema files
ln -sf "$BASEDIR/vendor/opus4-repo/search/conf/core.properties" "${SOLR_CORE_DIR}/data/solr"

ln -sf "${BASEDIR}/vendor/opus4-repo/search/conf/schema.xml" "${SOLR_CORE_DIR}/data/solr/conf"
ln -sf "${BASEDIR}/vendor/opus4-repo/search/conf/solrconfig.xml" "${SOLR_CORE_DIR}/data/solr/conf"

# provide logging properties
# TODO check integration of logging.properties with recent versions of solr
ln -sf "$BASEDIR/vendor/opus4-repo/search/conf/logging.properties" opus4/logging.properties

# detect URL prefix to use
SOLR_MAJOR="${SOLR_VERSION%%.*}"
case "$SOLR_MAJOR" in
5)
  SOLR_CONTEXT="/solr/solr"
  ;;
*)
  SOLR_CONTEXT="/solr"
esac

# run installer bundled with solr
bin/install_solr_service.sh "../downloads/$SOLR_ARCHIVE_NAME" -d "$SOLR_CORE_DIR" -i "$BASEDIR" -p "$SOLR_SERVER_PORT" -s solr

#
# Delete downloaded tar archive
#

[ -z "$DELETE_DOWNLOADS" ] && read -p "Delete downloads? [N]: " DELETE_DOWNLOADS
if [ "$DELETE_DOWNLOADS" = Y ] || [ "$DELETE_DOWNLOADS" = y ]; then
  rm -rf downloads
fi
