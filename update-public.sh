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

# Updates the OPUS4 *public* folder
# If the user is using a different theme than 'opus4'. The 'opus4' layout is 
# updated without further questions. If the user is using 'opus4' he is given
# the option of copying the current 'opus4' layout as a new theme, before the
# 'opus4' layout is updated.
#
# In short, the 'opus4' layout is always updated. User can save the old layout.

set -o errexit

source update-common.sh

setVars

PUBLIC_PATH=opus4/public
OLD_PUBLIC="$BASEDIR/$PUBLIC_PATH"
NEW_PUBLIC="$BASE_SOURCE/$PUBLIC_PATH"

OLD_CONFIG="$BASEDIR/opus4/application/configs"

echo "Updating directory $OLD_PUBLIC ..."

LAYOUTS="$OLD_PUBLIC/layouts"

getProperty "$OLD_CONFIG/config.ini" 'theme'
THEME="$PROP_VALUE"
THEME_OPUS='opus4' # "; theme = opus4"

echo "Selected theme: $THEME"
echo " Default theme: $THEME_OPUS"

# echo "Checking default layout (opus4) for modifications ..."
# TODO IMPORTANT use checkForModifications if opus4 layout has been modified

# Check if no theme or default theme has been configured
if [[ -z "$THEME" ]] || [[ "$THEME" == "$THEME_OPUS" ]]; then
    # Default theme is configured
    echo -e "You are currently using the standard OPUS4 layout. Any"
    echo -e " modifications you made to the layout will be lost during the"
    echo -e " update. Would you like to create a copy of the current layout"
    echo -e " under a different name (Y/n)? \c "
    read ANSWER
    if [[ -z "$ANSWER" ]]; then 
        ANSWER='y' # default is update layout
    else
        ANSWER=${ANSWER,,} # convert to lowercase
        ANSWER=${ANSWER:0:1} # get first letter
    fi
    if [[ "$ANSWER" == 'y' ]]; then
        # User wants to create backup of old layout folder

        # Ask for name of new theme
        while [[ -z "$THEME_NEW" ]] || [[ -d "$LAYOUTS/$THEME_NEW" ]]; do
            echo -e "Please enter name of new theme: \c "
            read THEME_NEW
            if [[ ! -z "$THEME_NEW" ]]; then
                # Check if layout folder already exists
                if [[ -d "$LAYOUTS/$THEME_NEW" ]]; then
                    # Folder already exists
                    echo "A theme with name '$THEME_NEW' already exists."
                fi
            fi
        done

        echo "Creating new theme '$THEME_NEW' ..."
        # Copy files from 'opus4' to new folder
        copyFolder "$LAYOUTS/$THEME_OPUS" "$LAYOUTS/$THEME_NEW"
        
        # Update configuration to use new theme
        setProperty "$OLD_CONFIG/config.ini" "theme" "$THEME_NEW"

        # TODO log to UPDATE.log
        echo "Your config.ini has been updated (theme = $THEME_NEW)."
    fi
fi 

# =============================================================================
# Update 'opus4' layout
# =============================================================================

# Add and replace files
updateFolder "$NEW_PUBLIC/layouts/$THEME_OPUS" "$LAYOUTS/$THEME_OPUS"
# Delete files no longer needed
deleteFiles "$NEW_PUBLIC/layouts/$THEME_OPUS" "$LAYOUTS/$THEME_OPUS"		

# Update other files
# TODO Should this be replace by "updateFolder SRC DEST flat" to handle all files in the folder
copyFile "$NEW_PUBLIC/htaccess-template" "$OLD_PUBLIC/htaccess-template"
copyFile "$NEW_PUBLIC/index.php" "$OLD_PUBLIC/index.php"