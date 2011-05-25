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

# Updates the OPUS4 configuration files

set -o errexit

# TODO move into common script? Be careful with main script.
BASEDIR="$1"
BASE_SOURCE="$2"
MD5_OLD="$3"
_UPDATELOG="$4"

source update-common.sh

DEBUG "BASEDIR = $BASEDIR"
DEBUG "BASE_SOURCE = $BASE_SOURCE"
DEBUG "MD5_OLD = $MD5_OLD"

DEST="$BASEDIR/opus4/application/configs"
MD5PATH=opus4/application/configs
SRC="$BASE_SOURCE/$MD5PATH"

echo "Updating configuration files ..."

DEBUG "Copying $SRC to $DEST"

# The following files are simply copied without checking the existing files.
copyFile "$SRC/application.ini" "$DEST/application.ini"
copyFile "$SRC/config.ini.template" "$DEST/config.ini.template"
copyFile "$SRC/doctypes/all.xml" "$DEST/doctypes/all.xml"
# TODO maybe config.ini should be merged with new template? Message to user?

# Ask user before replacing the following files if they have been modified.
updateFile "$SRC" "$DEST" "$MD5PATH" "navigation.xml"
updateFile "$SRC" "$DEST" "$MD5PATH" "navigationModules.xml"


