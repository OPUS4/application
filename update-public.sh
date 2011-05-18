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

# TODO the process for updating public seems confusing, especially for users that are happy with standard layout.
# TODO What if my_layout already exists?

set -o errexit

BASEDIR=$1
BASE_SOURCE=$2

source update-common.sh

PUBLIC_PATH=opus4/public
OLD_PUBLIC=$BASEDIR/$PUBLIC_PATH
NEW_PUBLIC=$BASE_SOURCE/$PUBLIC_PATH

OLD_CONFIG=$BASEDIR/opus4/application/configs

echo "Updating directory $OLD_PUBLIC ..."

LAYOUT_OPUS4=layouts/opus4
LAYOUT_CUSTOM=layouts/my_layout

getProperty $OLD_CONFIG/config.ini 'theme'
THEME=$PROP_VALUE
THEME_OPUS='opus4' # "; theme = opus4"

echo "Selected theme: $THEME"
echo " Default theme: $THEME_OPUS"

# Check if no theme or default theme has been configured
if [ -z "$THEME" ] || [ "$THEME" == "$THEME_OPUS" ]; then
    # Default theme is configured
    echo -e "You are currently using the standard OPUS4 layout. This creates "
    echo "conflicts during update process."
    echo -e "Would you like to copy the current layout to $LAYOUT_CUSTOM and "
    echo -e "update the standard opus4 layout [U] or skip important layout "
    echo -e "changes [s] [U|s]? : \c " 
    read LAYOUT_ANSWER
    if [ -z $LAYOUT_ANSWER ]; then 
        LAYOUT_ANSWER='u' # default is update layout
    else
        LAYOUT_ANSWER=${LAYOUT_ANSWER,,} # convert to lowercase
        LAYOUT_ANSWER=${LAYOUT_ANSWER:0:1} # get first letter
    fi
    if [ $LAYOUT_ANSWER = 's' ]; then
        echo "Please check layout changes/bugfixes in /opus4/public/layouts/opus4/" >> $CONFLICT 
    else 
        # TODO So every update leads to creation of new folder?
        if [ -d "$OLD_PUBLIC/$LAYOUT_CUSTOM" ]; then			
            read -p "The layout 'my_layout' already exists. Please enter a different layout name: " LAYOUT_NAME
            LAYOUT_CUSTOM=layouts/$LAYOUT_NAME			
        fi

        echo "Please update layout bugfixes manually in /opus4/public/$LAYOUT_CUSTOM" >> $CONFLICT
        createFolder $OLD_PUBLIC/$LAYOUT_CUSTOM
        # TODO use functions that log operations for this?
        copyFolder $OLD_PUBLIC/$LAYOUT_OPUS4 $OLD_PUBLIC/$LAYOUT_CUSTOM
        
        updateFolder $NEW_PUBLIC/$LAYOUT_OPUS4 $OLD_PUBLIC/$LAYOUT_OPUS4

        if [ ! -z $LAYOUT_NAME ]; then
            sed -i "s/; theme = opus4/theme = $LAYOUT_NAME/" $OLD_CONFIG/config.ini
        else 
            sed -i "s/; theme = opus4/theme = my_layout/" $OLD_CONFIG/config.ini
        fi
        echo "Your config.ini has changed => theme = $LAYOUT_NAME"
    fi
else
    # Custom theme has been configured
    # Add and replace files
    updateFolder $NEW_PUBLIC/$LAYOUT_OPUS4 $OLD_PUBLIC/$LAYOUT_OPUS4
    # Delete files no longer needed
    deleteFiles $NEW_PUBLIC/$LAYOUT_OPUS4 $OLD_PUBLIC/$LAYOUT_OPUS4
fi		

# Copy htaccess-template
# TODO What about local .htaccess file?
copyFile $NEW_PUBLIC/htaccess-template $OLD_PUBLIC/htaccess-template

# TODO handle index.php
