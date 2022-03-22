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
# @copyright   Copyright (c) 2022, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License

#
# Script for creating SOLR core for OPUS 4
#
# TODO there should be a better wau of doing this
#

set -e

if [[ $EUID -ne 0 ]]; then
    echo -e "\nERROR: This script must be run as root.\n" 1>&2
    exit 1
fi

SCRIPT_NAME="`basename "$0"`"
SCRIPT_NAME_FULL="`readlink -f "$0"`"
SCRIPT_PATH="`dirname "$SCRIPT_NAME_FULL"`"

BASEDIR="`dirname "$SCRIPT_PATH"`"

[ -z "$SOLR_USER" ] && read -p "Solr user [solr]: " SOLR_USER
SOLR_USER="${SOLR_USER:-solr}"

[ -z "$SOLR_DATA_PATH" ] && read -p "Solr data path [/var/solr/data]: " SOLR_DATA_PATH
SOLR_DATA_PATH="${SOLR_DATA_PATH:-/var/solr/data}"

[ -z "$SOLR_CORE_NAME" ] && read -p "Solr core name [opus4]: " SOLR_CORE_NAME
SOLR_CORE_NAME="${SOLR_CORE_NAME:-opus4}"

cd "$SOLR_DATA_PATH"

# create space for configuring solr service
mkdir -p "$SOLR_CORE_NAME/conf"

CORE_PATH="$SOLR_DATA_PATH/$SOLR_CORE_NAME"

# put configuration and schema files
ln -sf "$BASEDIR/vendor/opus4-repo/search/conf/core.properties" "${CORE_PATH}"

ln -sf "${BASEDIR}/vendor/opus4-repo/search/conf/schema.xml" "${CORE_PATH}/conf"
ln -sf "${BASEDIR}/vendor/opus4-repo/search/conf/solrconfig.xml" "${CORE_PATH}/conf"

# Set owner and group of files
chown -R $SOLR_USER:$SOLR_USER "$CORE_PATH"

# Restart solr (message)
systemctl restart $SOLR_USER
