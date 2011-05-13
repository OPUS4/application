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

set -o errexit

# TODO Maybe set based on argument or environment?
_DEBUG=1

# Executes parameter if DEBUG is enabled
# @param Text for output
function DEBUG() {
    # [ "$_DEBUG" -eq 1 ] && $@ # original version
    [ "$_DEBUG" -eq 1 ] && echo $@
    return 0 # required with *set -o errexit* TODO why?
}

# Adds entry to CONFLICTS.txt
# @param path to file that creates conflict
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
function updateFile {	
    DEST_PATH=$1
    SRC_PATH=$2
    MD5Path=$3
    FILE=$4
    if [ ! -f $DEST_PATH/$FILE ]; then
        echo -e "Copying $FILE ... \c "
        #the file does not exist in the old installation and can be copied
        cp $SRC_PATH/$FILE $DEST_PATH/$FILE
        echo "done"
    else 
        echo "Checking file $MD5Path/$FILE...."
        MD5ORIGIN=$(grep $MD5Path/$FILE $MD5_OLD | cut -b 1-32)
        MD5FILE=$(md5sum $DIR_O/$FILE | cut -b 1-32)
        if [ "$MD5ORIGIN" != "$MD5FILE" ]; then
            #the hashes are different
            DIFF='diff -b -B -q $DIR_O/$FILE $DIR_N/$FILE'
            if [ ${#DIFF} != 0 ]; then 
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

# Updates a folder 
# TODO implement
function updateFolder() {
    echo "TODO implement updateFolder"
}