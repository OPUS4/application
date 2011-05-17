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

# Updates the OPUS4 *import* directory

# TODO Is that sufficient for updating import folder?

set -o errexit

BASEDIR=$1
BASE_SOURCE=$2

source update-common.sh

echo -e "Updating *import* folder ... \c "

SRC=$BASE_SOURCE/opus4/import
DEST=$BASEDIR/opus4/import

# Create import folder if it does not exit yet
if [ ! -d $DEST ]; then
    createFolder $DEST
fi

# Copy files from source to destination folder
updateFolder $SRC $DEST
# Remove files from destination that do not exist in source folder
deleteFiles $SRC $DEST

echo "done"