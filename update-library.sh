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

# Updates the OPUS4 *library* directory

# integrate updateFolder with deleteFiles (make it a single call with parameters for different behaviour)
# TODO Are we possibly deleting too much in the next line? It will delete everything that does not exist in source location.
# TODO make sure folders are deleted, but not all of them (.svn)

set -o errexit

source update-common.sh

setVars

# Replace old framework folder with new one without checking for changes.
# TODO check folders

NEW_LIBRARY=$BASE_SOURCE/opus4/library
OLD_LIBRARY=$BASEDIR/opus4/library

DEBUG "NEW_LIBRARY = $NEW_LIBRARY"
DEBUG "OLD_LIBRARY = $OLD_LIBRARY"

echo -e "Updating *library* folder ... \c "

# Updating *Opus* folder
updateFolder $NEW_LIBRARY $OLD_LIBRARY
deleteFiles $NEW_LIBRARY $OLD_LIBRARY

echo "done"
