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
# @copyright   Copyright (c) 2010-2016, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License

#
# Downloads Apache Solr and runs the provides install script.
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

SOLR_SERVER_URL='http://archive.apache.org/dist/lucene/solr/5.3.1/solr-5.3.1.tgz'

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

# extract name of folder contained in archive
SOLR_DIR="$(tar tzf "downloads/$SOLR_ARCHIVE_NAME" | head -1)"
SOLR_DIR="${SOLR_DIR%%/*}"

SOLR_VERSION="${SOLR_DIR#solr-}"
SOLR_MAJOR="${SOLR_VERSION%%.*}"

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

# extract archive into basedir (expecting to create folder named solr-x.y.z)
echo "Extracting Solr archive ..."
tar xfz "downloads/$SOLR_ARCHIVE_NAME"
echo "done"

#
# Run Solr installation
#

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
ln -sf  "$BASEDIR/vendor/opus4-repo/search/core.properties" "${SOLR_CORE_DIR}/data/solr"

copyConfigFile "schema.xml" "${BASEDIR}/vendor/opus4-repo/search" "${SOLR_CORE_DIR}/data/solr/conf"
copyConfigFile "solrconfig.xml" "${BASEDIR}/vendor/opus4-repo/search" "${SOLR_CORE_DIR}/data/solr/conf"

# provide logging properties
# TODO check integration of logging.properties with recent versions of solr
ln -sf "$BASEDIR/vendor/opus4-repo/search/logging.properties" opus4/logging.properties

# detect URL prefix to use
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

