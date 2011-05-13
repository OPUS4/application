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

# Updates the OPUS4 *modules* directory

set -o errexit

BASEDIR=$1

source update-common.sh

OLD_MODULES=$BASEDIR/opus4/modules
NEW_MODULES1=opus4/modules
NEW_MODULES=../$NEW_MODULES1

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


