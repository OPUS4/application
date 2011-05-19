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
# @author      Susanne Gottwald <gottwald@zib.de>
# @author      Jens Schwidder <schwidder@zib.de>
# @copyright   Copyright (c) 2011, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id$

# Updates the OPUS4 database

# TODO explain what script does in few words
# TODO update database scripts only if necessary (see tickets)
# TODO use _DRYRUN to prevent execution of changes

set -o errexit
set -e

source update-common.sh

# TODO move into common script? Be careful with main script!
BASEDIR=$1
BASE_SOURCE=$2
VERSION_OLD=$3
VERSION_NEW=$4

SCHEMA_PATH=$BASE_SOURCE/opus4/db/schema

# TODO more flexible way to find mysql binary?
mysql_bin=/usr/bin/mysql
SCRIPT="$BASEDIR/opus4/db/createdb.sh"

#read database credentials from createdb.sh
# TODO Handle missing values
getProperty $SCRIPT user
USER="$PROP_VALUE"
getProperty $SCRIPT password
PASSWORD=$PROP_VALUE
getProperty $SCRIPT host
HOST=$PROP_VALUE
getProperty $SCRIPT port
PORT=$PROP_VALUE
getProperty $SCRIPT dbname
DBNAME=$PROP_VALUE

#recursive method selects database update script by the version numbers
#TODO: Replace recursive call by iteration over sorted files list
#TODO: new version really neccessary? Or just update to the newest version?
#@param $1 old version
#@param $2 new version
function dbScript() {
    V_OLD="$1"
    V_NEW="$2"
    #check if there are update scripts for origin version
    findFiles "$V_OLD"

    if [ "$FILES_COUNT" -eq 0 ]; then
        versionGroup "$V_OLD"
        #check if there are update scripts for group of origin version
        findFiles "$VERSION_GROUP"

        if [ "$FILES_COUNT" -eq 0 ]; then
            #no update scripts, neither for origin version nor version group
            echo "No information available for update from version $V_OLD to $V_NEW"
        else

            #a update file for the group of origin version was found
            if [ "$FILES_COUNT" -eq 1 ]; then
                #lists with more than one file are disregarded
                runDbUpdate "$FILES_LIST"
                FILENAME=$(basename "$FILES_LIST")
                findVersion "$FILENAME"

                if [ "$V_TO" != "$V_NEW" ]; then
                    #recursive call
                    dbScript "$V_TO" "$V_NEW"
                fi
            fi
        fi

     else

        #a update file for the origin version was found
        if [ "$FILES_COUNT" -eq 1 ]; then
            #lists with more than one file are disregarded
            runDbUpdate "$FILES_LIST"
            FILENAME=$(basename "$FILES_LIST")
            findVersion "$FILENAME"

            if [ "$V_TO" != "$V_NEW" ]; then
                #recursive call
                dbScript "$V_TO" "$V_NEW"
            fi
        fi
     fi
}

#method lists the files that concern the db update for the given version
#@param $1 version number
function findFiles() {
    VERSION="$1"
    FILES_LIST="$(find "$SCHEMA_PATH" -maxdepth 1 -type f -name "update-$VERSION*.sql")"
    FILES_COUNT="$(find "$SCHEMA_PATH" -maxdepth 1 -type f -name "update-$VERSION*.sql" | wc -l)"
    #DEBUG "Version = $VERSION + List = $FILES_LIST + Count = $FILES_COUNT"
}

#method extracts the new version from a db update script name
#@param $1 name of file
function findVersion() {
    FILE=$1
    #TODO: Find better way to extract the new version
    V_TO="$(echo "$FILE" | sed -e 's/^.*update-\(.*\)-to-\(.*\).sql$/\2/')"
    #DEBUG "File = $FILE + To-Version = $V_TO"
}

#method finds the "group" of versions for a specific version number
#e.g. 4.0.2 belongs to version group 4.0.x
#@param $1 version to find group for
function versionGroup() {
    VERSION=$1
    VERSION_PREFIX=$(echo $VERSION | cut -b 1-4)
    X=x
    VERSION_GROUP=$VERSION_PREFIX$X
}

#method executes a db update script (with global mysql credentials)
#@param $1 update script file
function runDbUpdate() {
    UPDATE_FILE=$1

    if [ "$_DRYRUN" -eq 0 ]; then
        MYSQL="${mysql_bin} --default-character-set=utf8 --user=${USER} --password=${PASSWORD} --host=${HOST} --port=${PORT}"

        if [ -n "${PASSWORD}" ]; then
            MYSQL="${MYSQL} --password=${PASSWORD}"
        fi    

    $MYSQL <<-EOFMYSQL
    USE $DBNAME;
    SOURCE $UPDATE_FILE;
EOFMYSQL

    fi
    DEBUG "MYSQL UPDATE SCRIPT = $UPDATE_FILE"
}

echo "Database is updating now..."
dbScript "$VERSION_OLD" "$VERSION_NEW"
echo "Database is up-to-date!"