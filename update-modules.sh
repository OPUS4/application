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
BASE_SOURCE=$2
MD5_OLD=$3

source update-common.sh

MODULES_PATH=opus4/modules
OLD_MODULES=$BASEDIR/opus4/modules
NEW_MODULES=$BASE_SOURCE/$MODULES_PATH

echo "Updating $OLD_MODULES ..."

# Get list of folder in modules directory => list of modules
MODULES=$(ls $NEW_MODULES)

# Copy all module directories and files, except views and language_custom
# TODO files in modules folder are not removed
# TODO IMPORTANT files in module folder are not removed
for MODULE in $MODULES; do
    LIST=$(ls $NEW_MODULES/$MODULE)
    for FILE in LIST; do
        # Check if folder and if special folder
        if [ -d "$FILE" ] && [ $FILE != 'views' ] && [ $FILE != 'language_custom' ]; then
            # Regular folder
            updateFolder $NEW_MODULES/$MODULE/$FILE $OLD_MODULES/$MODULE/$FILE;
        else
            # Check if file
            if [ -f "$FILE" ]; then 
                # Is file; copy it
                copyFile $NEW_MODULES/$MODULE/$FILE $OLD_MODULES/$MODULE/$FILE
            fi
        fi
    done	
done

# Special treatment for copying the view directories
HELPERS=helpers
SCRIPTS=scripts
VIEW=views

#1)copy all helpers directories
# TODO Why are helpers handled separately here? They are not ignored above?
for MODULE in $MODULES; do 
    if [ -d "$NEW_MODULES/$MODULE/$VIEW/$HELPERS" ]; then
        updateFolder $NEW_MODULES/$MODULE/$VIEW/$HELPERS $OLD_MODULES/$MODULE/$VIEW/$HELPERS;
    fi
done

#2)call filesDiff method for all files in all script directories
# TODO Does this work correctly? Can we ignore everything that is not executable?
# TODO Does not remove scripts.
for MODULE in $MODULES; do 
    if [ -d "$NEW_MODULES/$MODULE/$VIEW/$SCRIPTS" ]; then
        # TODO no better way to do this without cd?
        cd $NEW_MODULES/$MODULE/$VIEW/$SCRIPTS
        SCRIPT_FILES=$(find . -type f -exec ls {} \; | cut -b 3-)		
        cd $SCRIPT_PATH

        for FILE in $SCRIPT_FILES; do
            DEST=$OLD_MODULES/$MODULE/$VIEW/$SCRIPTS
            SRC=$NEW_MODULES/$MODULE/$VIEW/$SCRIPTS
            MD5PATH=$MODULES_PATH/$MODULE/$VIEW/$SCRIPTS
            updateFile $SRC $DEST $MD5PATH $FILE
        done		
    fi			
done

cd $SCRIPT_PATH

# TODO explain special handling of this one file
copyFile $NEW_MODULES/publish/$VIEW/$SCRIPTS/form/all.phtml $OLD_MODULES/publish/$VIEW/$SCRIPTS/form/all.phtml

echo ""


