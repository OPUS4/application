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
SCRIPTPATH=$4

source update-common.sh

MODULES_PATH=opus4/modules
OLD_MODULES=$BASEDIR/$MODULES_PATH
NEW_MODULES=$BASE_SOURCE/$MODULES_PATH

echo "Updating $OLD_MODULES ..."

# Get list of folder in modules directory => list of modules
MODULES=$(ls $NEW_MODULES)

# Copy all module directories and files, except views and language_custom
for MODULE in $MODULES; do
    DEBUG "Updating module $MODULE"
    # Check if folder exists in OPUS4 installation
    if [ ! -d $OLD_MODULES/$MODULE ]; then
        # New folder; create in old distribution
        createFolder $OLD_MODULES/$MODULE
    fi
    # Update folders of module
    LIST=$(ls $NEW_MODULES/$MODULE)
    for FILE in $LIST; do
        # Check if folder and if special folder
        if [ -d "$NEW_MODULES/$MODULE/$FILE" ]; then 
            # Check for special folder and update later
            if [ $FILE != 'views' ] && [ $FILE != 'language_custom' ]; then
                # Regular folder; update files
                updateFolder $NEW_MODULES/$MODULE/$FILE $OLD_MODULES/$MODULE/$FILE
                # Delete files not needed anymore
                deleteFiles $NEW_MODULES/$MODULE/$FILE $OLD_MODULES/$MODULE/$FILE
            fi
        else
            # Check if file
            if [ -f "$NEW_MODULES/$MODULE/$FILE" ]; then 
                # Is file; copy it
                copyFile $NEW_MODULES/$MODULE/$FILE $OLD_MODULES/$MODULE/$FILE
            fi
        fi
    done
    # Check if module exists in installed OPUS4
    # Should always exist because of code above, except in DRYRUN mode.
    if [ -d $OLD_MODULES/$MODULE ]; then 
        # Delete files in module root folder, not needed anymore
        DEBUG "Deleting files in $OLD_MODULES/$MODULE"
        deleteFiles $NEW_MODULES/$MODULE $OLD_MODULES/$MODULE flat
    fi
done

# Delete files and folders in modules directory
DEBUG "Deleting files and folders in $OLD_MODULES"
deleteFiles $NEW_MODULES $OLD_MODULES flat

# =============================================================================
# Special treatment for copying the view directories because the files there
# are more likely to be modified locally.
# =============================================================================

HELPERS=helpers
SCRIPTS=scripts
VIEW=views

# Copy all helpers directories
# The helpers folder is handled separately because it is a subfolder of views,
# but does not need to be updated using diff.
for MODULE in $MODULES; do 
    if [ -d "$NEW_MODULES/$MODULE/$VIEW/$HELPERS" ]; then
        updateFolder $NEW_MODULES/$MODULE/$VIEW/$HELPERS $OLD_MODULES/$MODULE/$VIEW/$HELPERS;
    fi
done

# Call updateFile function for all files in all script directories

# Iterate through modules
for MODULE in $MODULES; do 
    # Check if view/scripts folder exists for module
    if [ -d "$NEW_MODULES/$MODULE/$VIEW/$SCRIPTS" ]; then
        # List all files in scripts or on of its subfolders
        # TODO Better way without cd?
        cd $NEW_MODULES/$MODULE
        SCRIPT_FILES=$(find "$VIEW/$SCRIPTS" -type f -exec ls {} \;)		
        cd $SCRIPTPATH

        DEST=$OLD_MODULES/$MODULE
        SRC=$NEW_MODULES/$MODULE
        MD5PATH=$MODULES_PATH/$MODULE

        # Call updateFile for every file found
        for FILE in $SCRIPT_FILES; do
            # Get path to file
            FOLDER=$(dirname $DEST/$FILE)
            # Check if folder exists
            if [ ! -d $FOLDER ]; then
                # Folder does not exist; create it unless last folder created was the same
                if [ -z $PREV_FOLDER ] || [ $FOLDER != $PREV_FOLDER ]; then 
                    createFolder $FOLDER
                fi
                PREV_FOLDER=$FOLDER
            fi
            # Update file
            updateFile $SRC $DEST $MD5PATH $FILE
        done		
    fi			

    # Deletes files not needed anymore from scripts
    # TODO is this sufficient? Do we need to ask users?
    deleteFiles $NEW_MODULES/$MODULE/$VIEW/$SCRIPTS $OLD_MODULES/$MODULE/$VIEW/$SCRIPTS
done

cd $SCRIPTPATH

# TODO explain special handling of this one file
copyFile $NEW_MODULES/publish/$VIEW/$SCRIPTS/form/all.phtml $OLD_MODULES/publish/$VIEW/$SCRIPTS/form/all.phtml

