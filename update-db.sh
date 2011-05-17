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

set -o errexit

source update-common.sh

# TODO move into common script? Be careful with main script!
BASEDIR=$1
BASE_SOURCE=$2
VERSION_OLD=$3
VERSION_NEW=$4
mysql_bin=/usr/bin/mysql
SCRIPT=$BASEDIR/opus/db/createdb.sh

echo "Updating database ..."

SCHEMA_PATH=BASE_SOURCE/opus4/db/schema

VERSION_PREFIX=$(echo $VERSION_OLD | cut -b 1-4)
X=x
VERSION_X=$VERSION_PREFIX$X
SQL1="update-"$VERSION_OLD"-to-"$VERSION_NEW".sql"
SQL2="update-"$VERSION_X"-to-"$VERSION_NEW".sql"
UPDATE_FILE=$SQL1

if [ ! -f "$SCHEMA_PATH/$SQL1" ]
then
    if [ ! -f "$SCHEMA_PATH/$SQL2" ]
    then
	echo "No database update information available."
        exit 1
    else
        $UPDATE_FILE=$SQL2
    fi
fi

DEBUG "MYSQL UPDATE SCRIPT = $UPDATE_FILE"

#read database credentials from createdb.sh
USER=$ grep -v '^[[:space:]]*;' $SCRIPT | grep '^[[:space:]]*user[[:space:]]*=' | cut -d= -f2 | sed "s/\;.*$//; s/[ \'\"]*$//; s/^[ \'\"]*//"
PASSWORD=$ grep -v '^[[:space:]]*;' $SCRIPT | grep '^[[:space:]]*password[[:space:]]*=' | cut -d= -f2 | sed "s/\;.*$//; s/[ \'\"]*$//; s/^[ \'\"]*//"
HOST=$ grep -v '^[[:space:]]*;' $SCRIPT | grep '^[[:space:]]*host[[:space:]]*=' | cut -d= -f2 | sed "s/\;.*$//; s/[ \'\"]*$//; s/^[ \'\"]*//"
PORT=$ grep -v '^[[:space:]]*;' $SCRIPT | grep '^[[:space:]]*port[[:space:]]*=' | cut -d= -f2 | sed "s/\;.*$//; s/[ \'\"]*$//; s/^[ \'\"]*//"
DBNAME=$ grep -v '^[[:space:]]*;' $SCRIPT | grep '^[[:space:]]*dbname[[:space:]]*=' | cut -d= -f2 | sed "s/\;.*$//; s/[ \'\"]*$//; s/^[ \'\"]*//"

mysql="${mysql_bin} --default-character-set=utf8 --user=${USER} --host=${HOST} --port=${PORT}"

if [ -n "${PASSWORD}" ]; then
    mysql="${mysql} --password=${PASSWORD}"
fi
	
$MYSQL <<EOFMYSQL
USE $DBNAME;
SOURCE $SCHEMA_PATH/$UPDATE_FILE;

EOFMYSQL
	
echo "Database is up-to-date!"

