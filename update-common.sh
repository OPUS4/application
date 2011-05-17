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

# Common functions used by OPUS4 update scripts
# TODO consistent naming for functions (no idea whats common for shell scripts)

set -o errexit

# Enables (1) or disables (0) additional output for debugging.
# TODO Maybe set based on argument or environment?
_DEBUG=1

# Enables (1) or disables (0) dry run mode to create UPDATE.log without making
# any actual modifications to the OPUS4 installation.
# TODO Maybe set based on argument or environment?
_DRYRUN=0

# Executes parameter if DEBUG is enabled
# @param Text for output
function DEBUG() {
    # [ "$_DEBUG" -eq 1 ] && $@ # original version
    [ "$_DEBUG" -eq 1 ] && echo $@
    return 0 # required with *set -o errexit* TODO why?
}

# Writes operations into UPDATE.log 
# @param pathtofile
# @param operation
# TODO what if file already exists wenn update starts?
function UPDATELOG() {
    if [ -z $_UPDATELOG ]; then
        DEBUG "Setup UPDATE log"
        _UPDATELOG=$BASEDIR/UPDATE.log # TODO change name?
        if [ ! -f $_UPDATELOG ]; then  
            DEBUG "Write UPDATE log header"
            echo "Following operations were executed during the update:" > $_UPDATELOG
            echo "" >> $_UPDATELOG
        fi
    fi
    # Format output so that $2 is always at the same position
    printf "%-10s %s\n" $1 $2 >> $_UPDATELOG
}

# Adds entry to CONFLICTS.txt
# @param path to file that creates conflict
# TODO what if file already exists when update starts?
# TODO log conflicts to UPDATE.log
function addConflict() {
    if [ -z $CONFLICT ]; then
        DEBUG "Setup CONFLICT"
        CONFLICT=$BASEDIR/conflicts.txt # TODO change name to CONFLICTS.txt?
        if [ ! -f $CONFLICT ]; then  
            DEBUG "Write CONFLICT header"
            echo "Following files created conflicts and need to be changed manually:" > $CONFLICT
            echo "" >> $CONFLICT
        fi
    fi
    echo $@ >> $CONFLICT
}

# Checks and prompts user if files are different
# Using MD5 hashes to check if files have been modified.
# @param source folder 
# @param destination folder
# @param Path to MD5
# @param file
# Uses global variable MD5_OLD
# TODO use local SRC_FILE and DEST_FILE instead of construction over and over
function updateFile {	
    local SRC=$1
    local DEST=$2
    local MD5PATH=$3
    local FILE=$4
    if [ ! -f $DEST/$FILE ]; then
        # File does not exist at target destination and can be copied
        addFile $SRC/$FILE $DEST/$FILE
    else 
        # File already exists at target destination
        echo "Checking file $MD5PATH/$FILE for changes."

        # Get reference MD5 for file
        local MD5_REFERENCE=$(grep $MD5PATH/$FILE $MD5_OLD | cut -b 1-32)

        # Calculate MD5 for existing file
        local MD5_ACTUAL=$(md5sum $DEST/$FILE | cut -b 1-32)

        # Compare MD5 values
        if [ "$MD5_REFERENCE" != "$MD5_ACTUAL" ]; then
            # Hashes are different;

            # Check if changes are trivial (modified whitespace)
            local DIFF='diff -b -B -q $DEST/$FILE $SRC/$FILE'

            if [ ${#DIFF} != 0 ]; then # TODO IMPORTANT Why does the line look like comment (escape #?)
                # File was changed. User decides which file to keep.
                # TODO Add variable for automatic decision, for entire script, after first decision?
                echo "Conflict for $FILE"
                
                # TODO Add variable for printing out explanation only once.
                echo -e "You can keep the existing modified file and resolve the"
                echo -e " conflict after the update manually or the file can be"
                echo " replaced by the new file from OPUS4 $VERSION_NEW."
                
                # TODO Add option for more information
                echo -e "[K]eep modified file or [r]eplace with new file [K/r]? : \c " 
                read ANSWER # TODO How to make ANSWER local variable?

                # Check and format input
                if [ -z $ANSWER ]; then 
                    ANSWER='k'
                else 
                    ANSWER=${ANSWER,,}
                    ANSWER=${ANSWER:0:1}
                fi

                # TODO Check for invalid input? 
                if [ $ANSWER = 'r' ]; then
                    # Replace existing file
                    copyFile $SRC/$FILE $DEST/$FILE
                else
                    # Do not replace file; Log it as conflict
                    addConflict $DEST/$FILE
                fi
            else
                copyFile $SRC/$FILE $DEST/$FILE
            fi
        else
            # Installed file was not modified, replace it.
            copyFile $SRC/$FILE $DEST/$FILE
        fi
    fi
}

# Copies a file using different functions depending on existence of target file
function copyFile() {
    local SRC=$1
    local DEST=$2
    if [ ! -f $DEST ]; then 
        # target file does not exist
        addFile $SRC $DEST
    else 
        # target file already exists
        replaceFile $SRC $DEST
    fi
}

# Copies files from a source to a destination folder recursively
# TODO handle links
# TODO handle errors
# TODO handle symbolic link
# TODO check if source/target exist
# TODO handle errors
function updateFolder() {
    local SRC=$1
    local DEST=$2
    # Get files and folders in source directory
    local SRC_FILES=$(ls $SRC)
    # Iterate through files and folders
    for FILE in $SRC_FILES; do
        # Check if folder
        if [ -d $SRC/$FILE ]; then
            # Check if target folder exists
            if [ ! -d $DEST/$FILE ]; then
                # Create target folder if it does not exist already
                createFolder $DEST/$FILE
            fi
            # Call updateFolder recursively
            updateFolder $SRC/$FILE $DEST/$FILE
        else
            copyFile $SRC/$FILE $DEST/$FILE
        fi
    done
}

# Deletes files that exist at destination but not in source folder recursively
# TODO IMPORTANT handle/ignore symbolic links
# TODO filter deletes based on MD5 list (does that actually work as expected)?
function deleteFiles() {
    local SRC=$1
    local DEST=$2
    local DEST_FILES=$(ls $DEST)
    # Iterate through destination files
    for FILE in $DEST_FILES; do
        # Check if folder
        if [ -d $DEST/$FILE ]; then
            # Check if folder exists in source folder
            if [ ! -d $SRC/$FILE ]; then
                # Folder does not exist
                # TODO Delete folder file by file recursively (for log) 
                deleteFolder -rf $DEST/$FILE
            else
                # Folder exists, call deleteFiles recursively
                deleteFiles $SRC/$FILE $DEST/$FILE
            fi
        else
            # Check if file exists in source folder
            if [ ! -f $SRC/$FILE ]; then
                # File does not exist; Delete file in destination folder
                deleteFile $DEST/$FILE
            fi 
        fi
    done
}

# TODO add console output to the following functions performing operations?

# Adds a new file to the OPUS4 installation
function addFile() {
    cp $1 $2
    UPDATELOG "ADDED" $2
    DEBUG "Added file $2"
}

# Updates an unmodified file of the OPUS4 installation
function replaceFile() {
    cp $1 $2
    UPDATELOG "REPLACED" $2
    DEBUG "Replaced file $2"
}

# Deletes a file from the OPUS4 installation
function deleteFile() {
    rm $1
    UPDATELOG "DELETED" $1
    DEBUG "Deleted file $1"
}

# Deletes a folder from the OPUS4 installation
function deleteFolder() {
    rm -rf $1
    UPDATELOG "DELETED" $1
    DEBUG "Deleted folder $1"
}

# Creates a folder
function createFolder() {
    mkdir $1
    UPDATELOG "CREATED" $1
    DEBUG "Created folder $1"
}

# Replaces a modified file in the OPUS4 installation
function replaceModifiedFile() {
    echo "TODO implement REPLACE MODIFIED file"
}

BASEDIR=./test

updateFolder $1 $2
deleteFiles $1 $2