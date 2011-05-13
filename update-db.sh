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

echo "Updating database ..."

################################################################
#Part 7: update mysql database schema
################################################################
echo "Checking database update information...."
echo "***************************************************"

# aus Zeitmangel nur für Update auf 4.1 zu verwenden
# ermöglicht keinen rekursiven Aufruf anderer Skripte!!!
# muss bei nächster Datenbankänderung angepasst werden! 

SCHEMA_PATH=../opus4/db/schema
cd $SCHEMA_PATH
SCHEMA_PATH=`pwd`
VERSION_1=$(echo $VERSION_OLD | cut -b 1-4)
X=x
VERSION_X=$VERSION_1$X
#SQL="update-"$VERSION_X"-to-"$VERSION_NEW".sql"
SQL="update-"$VERSION_X"-to-4.1.0.sql"

if [ ! -f "$SCHEMA_PATH/$SQL" ]
then 
	echo "No database update information available."	
	echo "Thanks for updating OPUS! Have fun with it!"
	exit 1
fi

echo "The database is updating now."
echo "*******************************************"
OPUS_DB=$(grep db.params.dbname $OLD_CONFIG/config.ini | cut -b 20-)
HOST=$(grep db.params.host $OLD_CONFIG/config.ini | cut -b 18-)

read -p "MySQL Root User [root]: " MYSQLROOT
if [ -z "$MYSQLROOT" ]
then
	MYSQLROOT=root
fi
if [ $HOST=="''" ]
then 
	MYSQL="$MYSQL_CLIENT --default-character-set=utf8 -u $MYSQLROOT -v -p"
else 
	MYSQL="$MYSQL_CLIENT --default-character-set=utf8 -u $MYSQLROOT -h $HOST -v -p"
fi
	
$MYSQL <<EOFMYSQL
USE $OPUS_DB;
SOURCE $SCHEMA_PATH/$SQL;

EOFMYSQL
	
echo "Database is up-to-date!"
echo "Thanks for updating OPUS! Have fun with it!"

