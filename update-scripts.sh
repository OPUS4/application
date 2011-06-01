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

# Updates the OPUS4 *scripts* folder

set -o errexit

source update-common.sh

setVars

SCRIPTS_PATH=opus4/scripts
OLD_SCRIPTS="$BASEDIR/$SCRIPTS_PATH"
NEW_SCRIPTS="$BASE_SOURCE/$SCRIPTS_PATH"

echo -e "Updating $OLD_SCRIPTS ... \c "
# Files in the scripts folder are updated without checks
updateFolder "$NEW_SCRIPTS" "$OLD_SCRIPTS"
# Files that are not part of new distribution are deleted
deleteFiles "$NEW_SCRIPTS" "$OLD_SCRIPTS"

# Update opus-apache-rewritemap-caller-secure.sh
FILE="opus-apache-rewritemap-caller-secure.sh"
# Make backup of old file
getProperty "$OLD_SCRIPTS/$FILE" "USER"
USER_VALUE=$PROP_VALUE
copyFile "$OLD_SCRIPTS/$FILE" "$OLD_SCRIPTS/$FILE.backup.$VERSION_OLD"

DEBUG "Replacing $FILE."
DEBUG "USER = $USER_VALUE"

# Copy template
copyFile "$OLD_SCRIPTS/$FILE.template" "$OLD_SCRIPTS/$FILE"
setProperty2 "$OLD_SCRIPTS/$FILE" "USER" "$USER_VALUE"

echo "done"

