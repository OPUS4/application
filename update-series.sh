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
# @copyright   Copyright (c) 2012, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id$


# Migration of Collection-based series (were eliminated with OPUS 4.2.0)

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
UPDATESERIESLOG="$BASEDIR/UPDATE-series.log"



##############################################
## begin: duplicated code from update-db.sh ##
##############################################

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


#method executes a db update script (with global mysql credentials)
#@param $1 update script file
function runDbUpdate() {
    UPDATE_FILE=$1

    if ! DRYRUN ; then
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

############################################
## end: duplicated code from update-db.sh ##
############################################



# Ensure this is only done for updates from versions < 4.2.0
if [[ "$VERSION_OLD" < "4.2" && "$VERSION_NEW" > "4.2" ]]; then
   echo -e "Would you like to migrate your old series (y/N)? \c ";
   read ANSWER
   if [[ -z $ANSWER ]]; then
      ANSWER='n'
   else
      if [[ $ANSWER == 'y' ]]; then
         # inform user which series documents have no IdentifierSerial
         "$BASEDIR/opus4/scripts/series_migration/FindMissingSeriesNumbers.php" "$UPDATESERIESLOG"
         # run migration script
         runDbUpdate "$SCHEMA_PATH/update-series-for-4.2.0.sql"
      else
         echo "Keep the old series."
      fi
   fi
fi
