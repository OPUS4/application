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

set -o errexit

source update-common.sh

setVars

DEBUG "BASEDIR = $BASEDIR"
DEBUG "BASE_SOURCE = $BASE_SOURCE"
DEBUG "MD5_OLD = $MD5_OLD"
DEBUG "VERSION_NEW = $VERSION_NEW"
DEBUG "VERSION_OLD = $VERSION_OLD"
DEBUG "_UPDATELOG = $_UPDATELOG"

SCHEMA_PATH="$BASE_SOURCE/opus4/db/schema"

# TODO more flexible way to find mysql binary?
mysql_bin=/usr/bin/mysql
mysql_dump=/usr/bin/mysqldump
SCRIPT="$BASEDIR/opus4/db/createdb.sh"
UPDATED=0

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
#TODO more debug output to see the steps
#@param $1 old version
#@param $2 new version
function dbScript() {
    local V_OLD="$1"
    local V_NEW="$2"
    #check if there are update scripts for origin version
    findFiles "$V_OLD"

    if [[ "$FILES_COUNT" -eq 0 ]]; then
        versionGroup "$V_OLD"
        #check if there are update scripts for group of origin version
        findFiles "$VERSION_GROUP"

        if [[ "$FILES_COUNT" -eq 0 ]]; then
            #no update scripts, neither for origin version nor version group
            echo "No information available for update from version $V_OLD to $V_NEW"
        else

            #a update file for the group of origin version was found
            if [[ "$FILES_COUNT" -eq 1 ]]; then
                #lists with more than one file are disregarded
                runDbUpdate "$FILES_LIST"
                FILENAME=$(basename "$FILES_LIST")
                findVersion "$FILENAME"

                if [[ "$V_TO" != "$V_NEW" ]]; then
                    #recursive call
                    dbScript "$V_TO" "$V_NEW"
                fi
            fi
        fi

     else

        #a update file for the origin version was found
        if [[ "$FILES_COUNT" -eq 1 ]]; then
            #lists with more than one file are disregarded
            runDbUpdate "$FILES_LIST"
            FILENAME=$(basename "$FILES_LIST")
            findVersion "$FILENAME"

            if [[ "$V_TO" != "$V_NEW" ]]; then
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
    VERSION="$1"
    VERSION_PREFIX="$(echo "$VERSION" | cut -b 1-4)"
    X=x
    VERSION_GROUP="$VERSION_PREFIX""$X"
}

#method executes a db update script (with global mysql credentials)
#@param $1 update script file
function runDbUpdate() {
    UPDATE_FILE=$1

    if [[ DRYRUN ]]; then
        MYSQL="${mysql_bin} --default-character-set=utf8 --user=${USER} --password=${PASSWORD} --host=${HOST} --port=${PORT}"

        if [[ -n "${PASSWORD}" ]]; then
            MYSQL="${MYSQL} --password=${PASSWORD}"
        fi    

    $MYSQL <<-EOFMYSQL
    USE $DBNAME;
    SOURCE $UPDATE_FILE;
EOFMYSQL

    fi
    DEBUG "MYSQL UPDATE SCRIPT = $UPDATE_FILE"    
    echo "$UPDATE_FILE" >> "$BASE_SOURCE"/dbupdated.txt
}

## backup old database (even in dry-run mode!)
#@param $1 old database version
function runDbBackup() {
    VERSION_OLD=$1

    MYSQLDUMP="${mysql_dump} --default-character-set=utf8 --user=${USER} --password=${PASSWORD} --host=${HOST} --port=${PORT}  $DBNAME"
    if [ -n "${PASSWORD}" ]; then
        MYSQLDUMP="${MYSQLDUMP} --password=${PASSWORD}"
    fi

    BACKUP_FILENAME="$BASEDIR/opus4/db/mysqldump-$DBNAME--$VERSION_OLD--$(date -Iseconds).sql"
    DEBUG "MYSQL BACKUP = $BACKUP_FILENAME"

    $MYSQLDUMP >"$BACKUP_FILENAME"
}


echo "Backing up database..."
runDbBackup "$VERSION_OLD"
echo "Backing up database... done."

echo "Database is updating now..."
dbScript "$VERSION_OLD" "$VERSION_NEW"
echo "Database is up-to-date!"

# Copy sql files from source to destination folder
updateFolder "$SCHEMA_PATH" "$BASEDIR"/opus4/db/schema
updateFolder "$BASE_SOURCE"/opus4/db/masterdata "$BASEDIR"/opus4/db/masterdata

# Update createdb.sh.template
copyFile "$BASE_SOURCE/opus4/db/createdb.sh.template" "$BASEDIR/opus4/db/createdb.sh.template"

# Update createdb.sh
FILE_PATH=opus4/db/createdb.sh
FILE="$BASEDIR/$FILE_PATH"

# get properties from old file
# TODO make nicer
getProperty "$FILE" "user"
CREATEDB_USER=$PROP_VALUE

getProperty "$FILE" "password"
CREATEDB_PASSWORD=$PROP_VALUE

getProperty "$FILE" "host"
CREATEDB_HOST=$PROP_VALUE

getProperty "$FILE" "port"
CREATEDB_PORT=$PROP_VALUE

getProperty "$FILE" "dbname"
CREATEDB_DBNAME=$PROP_VALUE

getProperty "$FILE" "mysql_bin"
CREATEDB_MYSQL_BIN=$PROP_VALUE

getProperty "$FILE" "master_dir"
CREATEDB_MASTER_DIR=$PROP_VALUE

# Create backup of current createdb.sh
copyFile "$FILE" "$FILE.backup.$VERSION_OLD"

# Copy template
copyFile "$FILE.template" "$FILE"

# Set properties
# TODO only modify files if DRYRUN is disabled
setProperty2 "$FILE" "user" "$CREATEDB_USER"
setProperty2 "$FILE" "password" "$CREATEDB_PASSWORD"
setProperty2 "$FILE" "host" "$CREATEDB_HOST"
setProperty2 "$FILE" "port" "$CREATEDB_PORT"
setProperty2 "$FILE" "dbname" "$CREATEDB_DBNAME"
setProperty2 "$FILE" "mysql_bin" "$CREATEDB_MYSQL_BIN"
setProperty2 "$FILE" "master_dir" "$CREATEDB_MASTER_DIR"
