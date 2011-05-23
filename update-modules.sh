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

# Update process for modules:
# For each module every file found is checked against the MD5 file. If the file
# was part of the old distribution and was not changed it is deleted. Empty 
# folders are deleted. Afterwards the new files are transferred to the module 
# folder. If a file already exists, the existing file is renamed to 
# FILENAME.backup and replaced by the new file.
# The resulting folder contains all new files, backups of old modified files,
# and any extra files created by the user.
# 
# Short version:
# Unmodified files => Replace with new files
# Modified files   => Rename to FILENAME.backup and replace with new files
# Unknown files    => Keep
# 
# TODO Explain how files are handled that were removed from the distribution?

set -o errexit

BASEDIR="$1"
BASE_SOURCE="$2"
MD5_OLD="$3"
MD5_NEW="$4"
SCRIPTPATH="$5"

source update-common.sh

MODULES_PATH="opus4/modules"
OLD_MODULES="$BASEDIR/$MODULES_PATH"
NEW_MODULES="$BASE_SOURCE/$MODULES_PATH"

echo "Updating $OLD_MODULES ..."

# =============================================================================
# Update or delete existing files
# =============================================================================

# Iterate through module files
find "$OLD_MODULES" -type f -print0 | while read -r -d $'\0' FILE_PATH; do
    FILE=$(echo "$FILE_PATH" | sed -e "s|$OLD_MODULES/||") 
    DEBUG "Update $FILE"
    
    # Get reference MD5 for file
    FILE_MD5_REFERENCE="$(getMD5 $MODULES_PATH/$FILE $MD5_OLD)"
    DEBUG "MD5 ref = $FILE_MD5_REFERENCE"

    # Calculate MD5 for existing file
    FILE_MD5_ACTUAL="$(getActualMD5 $OLD_MODULES/$FILE)"
    DEBUG "MD5 cur = $FILE_MD5_ACTUAL" # TODO Why get spaces lost?

    # Get reference MD5 for new file
    FILE_MD5_NEW="$(getMD5 $MODULES_PATH/$FILE $MD5_NEW)"
    DEBUG "MD5 new = $FILE_MD5_NEW"

    # Check if file is part of old distribution (MD5 reference exists)
    if [ ! -z "$FILE_MD5_REFERENCE" ]; then
        # MD5 reference found; File part of old distribution
        # Check if File was modified
        if [ "$FILE_MD5_REFERENCE" == "$FILE_MD5_ACTUAL" ]; then
            # File was not modified
            # Check if new version exists
            if [ ! -z "$FILE_MD5_NEW" ]; then
                # New version of file exists; Replace it
                copyFile "$NEW_MODULES/$FILE" "$OLD_MODULES/$FILE"
            else 
                # File no longer part of new distribution; Delete it
                deleteFile "$OLD_MODULES/$FILE"
            fi
        else 
            # File was modified
            # Check if new version exists
            if [ ! -z "$FILE_MD5_NEW" ]; then
                # New version of file exists; Rename file, copy new file
                renameFile "$OLD_MODULES/$FILE" "$OLD_MODULES/$FILE.backup"
                # Copy new file
                copyFile "$NEW_MODULES/$FILE" "$OLD_MODULES/$FILE"
            else
                # File no longer part of new distribution; Rename file
                renameFile "$OLD_MODULES/$FILE" "$OLD_MODULES/$FILE.backup"
            fi
        fi
    else
        # MD5 reference not found; File not part of old distribution
        # Check if new distribution contains file 
        if [ ! -z "$FILE_MD5_NEW" ]; then
            # New distribution contains file; Rename file, copy new file
            renameFile "$OLD_MODULES/$FILE" "$OLD_MODULES/$FILE.backup"
            # Copy new file
            copyFile "$NEW_MODULES/$FILE" "$OLD_MODULES/$FILE"
        else 
            # File not part of new distribtution either
            DEBUG "Unknown file $FILE (do nothing)"
        fi
    fi
    
done

# =============================================================================
# Delete empty folders
# =============================================================================

# Iterate through found empty folders
find "$OLD_MODULES" -type d -empty -print0 | while read -r -d $'\0' FILE_PATH; do
    FILE=$(echo "$FILE_PATH" | sed -e "s|$OLD_MODULES/||") 
    DEBUG "Empty folder $FILE found"
    # Delete empty folders
    # TODO Find way to add message like "(EMPTY)" to UPDATE.log
    deleteFolder "$OLD_MODULES/$FILE"
done

# =============================================================================
# Add new files from the new distribution
# =============================================================================

# Iterate through all new modules files
find "$NEW_MODULES" -type f -print0 | while read -r -d $'\0' FILE_PATH; do
    FILE=$(echo "$FILE_PATH" | sed -e "s|$NEW_MODULES/||") 
    # Check if file does not exist in old modules folder
    # If file exists it has been already processed above.
    if [ ! -f "$OLD_MODULES/$FILE" ]; then
        # File does not exist in old folder; Add it
        copyFile "$NEW_MODULES/$FILE" "$OLD_MODULES/$FILE"
    fi
done