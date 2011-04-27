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
# @copyright   Copyright (c) 2011, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id$

#set -ex
set -e

SCRIPTNAME=`basename $0`
SCRIPT_PATH=$(cd `dirname $0` && pwd)
BASEDIR=/var/local/opus4
MYSQL_CLIENT=/usr/bin/mysql
cd $SCRIPT_PATH
VERSION_NEW=$(sed -n '1p' ../VERSION.txt)
MD5_NEW=../MD5SUMS

# check if opus4 can be found at normal path 
if [ ! -d $BASEDIR ]
then
	echo "OPUS4 cannot be found at $BASEDIR."
	read -p "Please enter the path to the installation directory of OPUS4: " BASEDIR_NEW
	
	if [ -z $BASEDIR_NEW ]
	then 
		echo "Empty path: please check the path of your OPUS4 installation for an update or run the install script"
		exit 1
	else
		while [ ! -d $BASEDIR_NEW ] && [ $BASEDIR_NEW != "y" ] && [ $BASEDIR_NEW != "Y" ]
		do
			read -p "OPUS could not be found, wrong path? Enter path again or do you wish to abort? [y]: " BASEDIR_NEW			
		done
		
		if [ $BASEDIR_NEW = 'y' ]
		then 
			echo "Aborting..."
			exit 1
		else 
			BASEDIR=$BASEDIR_NEW			
		fi
	fi
fi

#read installation version from file or prompt 
if [ ! -f $BASEDIR/VERSION.txt ]
then
	read -p "Which OPUS version is installed?: " VERSION_OLD
else
	VERSION_OLD=$(sed -n '1p' $BASEDIR/VERSION.txt)		
fi

#find file with MD5Sums of installation version 
if [ ! -f $BASEDIR/MD5SUMS ]
then
	MD5_OLD=../releases/$VERSION_OLD.MD5SUMS
else
	MD5_OLD=$BASEDIR/MD5SUMS
fi
	
echo "Notice: You should backup your OPUS $VERSION_OLD installation first, files will be overwritten....."
read -p "Start the update to OPUS $VERSION_NEW now? [y/n]: " UPDATE_NOW
if [ -z $UPDATE_NOW ] || [ $UPDATE_NOW = 'y' ] || [ $UPDATE_NOW = 'Y' ]
then 	
	echo ""
else 
	echo "Aborting..."
	exit 1
fi

# names of directories in the OLD opus4 installation
OLD_CONFIG=$BASEDIR/opus4/application/configs
OLD_FRAMEWORK=$BASEDIR/opus4/library/Opus
OLD_MODULES=$BASEDIR/opus4/modules
OLD_PUBLIC=$BASEDIR/opus4/public
OLD_SCRIPTS=$BASEDIR/opus4/scripts

# names of directories in the NEW opus4 source directory
NEW_CONFIG1=opus4/application/configs
NEW_CONFIG=../$NEW_CONFIG1

NEW_FRAMEWORK1=opus4/library/Opus
NEW_FRAMEWORK=../$NEW_FRAMEWORK1

NEW_MODULES1=opus4/modules
NEW_MODULES=../$NEW_MODULES1

NEW_PUBLIC1=opus4/public
NEW_PUBLIC=../$NEW_PUBLIC1

NEW_SCRIPTS1=opus4/scripts
NEW_SCRIPTS=../$NEW_SCRIPTS1

#create txt file for conflicts
CONFLICT=$BASEDIR/conflicts.txt
echo "Following files created conflicts and need to be changed manually:" > $CONFLICT
echo "" >> $CONFLICT

#function for checking and prompting if files are different
#comparing the md5 hashes of each file
function filesDiff {	
	if [ ! -f $DIR_O/$FILE ]
	then
		#the new file does not exist in the old installation and can be copied
		cp $DIR_N/$FILE $DIR_O/$FILE
	else 
		echo "Checking file $MD5Path/$FILE...."
		MD5ORIGIN=$(grep $MD5Path/$FILE $MD5_OLD | cut -b 1-32)
		MD5FILE=$(md5sum $DIR_O/$FILE | cut -b 1-32)
		if [ "$MD5ORIGIN" != "$MD5FILE" ]
		then
			#the hashes are different
			DIFF='diff -b -B -q $DIR_O/$FILE $DIR_N/$FILE'
			if [ ${#DIFF} != 0 ]
			then
				#files are different and the user is asked if he wants to update the file
				read -p "Conflict for $FILE ! Solve the conflict manually after update? [1] Copy the new file now? [2] : " ANSWER		
			
				if [ $ANSWER = '2' ]
				then 
					cp $DIR_N/$FILE $DIR_O/$FILE
				else 
					#file in which conflicts are stored for later 
					echo $DIR_O/$FILE >> $CONFLICT
				fi
			else
				 cp $DIR_N/$FILE $DIR_O/$FILE
			fi
		else
			cp $DIR_N/$FILE $DIR_O/$FILE
		fi
	fi
}

################################################################
# Part 1: update framework (without diff checking)
################################################################
echo "The directory $OLD_FRAMEWORK is now replaced by the new files."
echo "*****************************************************************************************"
cp $NEW_FRAMEWORK/* -R $OLD_FRAMEWORK
echo ""

################################################################
# Part 2: update configs directory (with and without diff)
################################################################
echo "The directory $OLD_CONFIG is updating now."
echo "***********************************************************************"
cp $NEW_CONFIG/application.ini $OLD_CONFIG/application.ini
cp $NEW_CONFIG/config.ini.template $OLD_CONFIG/config.ini.template
cp $NEW_CONFIG/doctypes/all.xml $OLD_CONFIG/doctypes/all.xml

DIR_O=$OLD_CONFIG
DIR_N=$NEW_CONFIG
MD5Path=$NEW_CONFIG1

FILE=navigation.xml
filesDiff

FILE=navigationModules.xml
filesDiff
echo ""

################################################################
#Part 3: update modules (with and without diff)
################################################################
echo "The directory $OLD_MODULES is updating now."
echo "********************************************************************"

MODULES=$(ls $NEW_MODULES )

#copy all module directories and files, except views and language_custom
for i in $MODULES;
do
	LIST=$(ls $NEW_MODULES/$i)
	for j in LIST
	do
		if [ -d "$j" ] && [ $j != 'views' ] && [ $j != 'language_custom' ]
		then
			cp $NEW_MODULES/$i/$j/* -R $OLD_MODULES/$i/$j;
		else
			if [ -f "$j" ]
			then
				cp $NEW_MODULES/$i/$j $OLD_MODULES/$i/$j
			fi
		fi
	done	
done

#special treatment for copying the view directories
HELPERS=helpers
SCRIPTS=scripts
VIEW=views

#1)copy all helpers directories
for i in $MODULES;
do 
	if [ -d "$NEW_MODULES/$i/$VIEW/$HELPERS" ]
	then
		cp $NEW_MODULES/$i/$VIEW/$HELPERS/* -R $OLD_MODULES/$i/$VIEW/$HELPERS;
	fi
done

#2)call filesDiff method for all files in all script directories
for i in $MODULES;
do 
	if [ -d "$NEW_MODULES/$i/$VIEW/$SCRIPTS" ]
	then
		cd $NEW_MODULES/$i/$VIEW/$SCRIPTS
		SCRIPT_FILES=$(find . -type f -exec ls {} \; | cut -b 3-)		
		cd $SCRIPT_PATH
		for j in $SCRIPT_FILES;
		do
			DIR_O=$OLD_MODULES/$i/$VIEW/$SCRIPTS
			DIR_N=$NEW_MODULES/$i/$VIEW/$SCRIPTS
			MD5Path=$NEW_MODULES1/$i/$VIEW/$SCRIPTS
			FILE=$j
			filesDiff
		done		
	fi			
done

cd $SCRIPT_PATH
cp $NEW_MODULES/publish/$VIEW/$SCRIPTS/form/all.phtml $OLD_MODULES/publish/$VIEW/$SCRIPTS/form/all.phtml
echo ""

################################################################
#Part 4: update scripts directory (without diff)
################################################################
echo "The directory $OLD_SCRIPTS is updating now."
echo "************************************************************"
cp $NEW_SCRIPTS/* -R $OLD_SCRIPTS
echo ""

################################################################
#Part 5: update public directory (depends)
################################################################
echo "The directory $OLD_PUBLIC is updating now."
echo "**************************************************************"

LAYOUT_OPUS4=layouts/opus4
LAYOUT_CUSTOM=layouts/my_layout

THEME1=$(grep 'theme = ' $OLD_CONFIG/config.ini)
THEME_OPUS="; theme = opus4"
echo $THEME1
echo $THEME_OPUS

if [ -z "$THEME1" ] || [ "$THEME1" == "$THEME_OPUS" ]
then
	echo "You are currently using the standard OPUS4 layout. This creates conflicts during update process."
	read -p "Do you want to keep the current layout as 'my_layout' and update the standard opus4 layout [1] or skip important layout changes [2] ? : " LAYOUT_ANSWER
	if [ $LAYOUT_ANSWER = '2' ]
	then
		echo "Please check layout changes/bugfixes in /opus4/public/layouts/opus4/" >> $CONFLICT 
	else 
		if [ -d "$OLD_PUBLIC/$LAYOUT_CUSTOM" ]
		then			
			read -p "The layout 'my_layout' already exists. Please enter a different layout name: " LAYOUT_NAME
			LAYOUT_CUSTOM=layouts/$LAYOUT_NAME			
		fi
		
		echo "Please update layout bugfixes manually in /opus4/public/$LAYOUT_CUSTOM" >> $CONFLICT
		mkdir $OLD_PUBLIC/$LAYOUT_CUSTOM
		cp $OLD_PUBLIC/$LAYOUT_OPUS4/* -R $OLD_PUBLIC/$LAYOUT_CUSTOM
		cp $NEW_PUBLIC/$LAYOUT_OPUS4/* -R $OLD_PUBLIC/$LAYOUT_OPUS4
		
		if [ ! -z $LAYOUT_NAME ]
		then
			sed -i "s/; theme = opus4/theme = $LAYOUT_NAME/" $OLD_CONFIG/config.ini
		else 
			sed -i "s/; theme = opus4/theme = my_layout/" $OLD_CONFIG/config.ini
		fi
		echo "Your config.ini has changed => theme = $LAYOUT_NAME"
	fi
else
	cp $NEW_PUBLIC/$LAYOUT_OPUS4/* -R $OLD_PUBLIC/$LAYOUT_OPUS4
fi		

cp $NEW_PUBLIC/htaccess-template $OLD_PUBLIC/htaccess-template
echo ""

################################################################
#Part 6: update SOLR Server, rebuild index (with diff)
################################################################
if [ $VERSION_OLD==4.0.0 ] || [ $VERSION_OLD==4.0.1 ] || [ $VERSION_OLD==4.0.2 ]
then
	echo "The Solr server schema has to be updated."
	echo "*******************************************"
	DIR_O=$BASEDIR/solrconfig
	DIR_N=../solrconfig
	FILE=schema.xml
	filesDiff
	echo "The Solr index is rebuilding now..."
	php5 $OLD_SCRIPTS/SolrIndexBuilder.php
fi
echo ""

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
