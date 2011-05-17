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

# TODO Better way of updating library folder except Application folder.
# TODO Folders in *library* (top-level) that are not used anymore are not deleted.

set -o errexit

source update-common.sh

# TODO Move into common script? Be careful with main script!
BASEDIR=$1
BASE_SOURCE=$2

# Replace old framework folder with new one without checking for changes.
# TODO check folders

NEW_LIBRARY=$BASE_SOURCE/opus4/library
OLD_LIBRARY=$BASEDIR/opus4/library

DEBUG "NEW_LIBRARY = $NEW_LIBRARY"
DEBUG "OLD_LIBRARY = $OLD_LIBRARY"

echo -e "Updating *library* folder ... \c "

# Updating *Opus* folder
updateFolder $NEW_LIBRARY/Opus $OLD_LIBRARY/Opus
# TODO Are we possibly deleting too much in the next line? It will delete everything that does not exist in source location.
deleteFiles $NEW_LIBRARY/Opus $OLD_LIBRARY/Opus
# TODO verify with diff between $NEW_FRAMEWORK and $OLD_FRAMEWORK?

# Updating *Form* folder
updateFolder $NEW_LIBRARY/Form $OLD_LIBRARY/Form
deleteFiles $NEW_LIBRARY/Form $OLD_LIBRARY/Form

# Updating *Controller* folder
updateFolder $NEW_LIBRARY/Controller $OLD_LIBRARY/Controller
deleteFiles $NEW_LIBRARY/Controller $OLD_LIBRARY/Controller

# Updating *Rewritemap* folder
updateFolder $NEW_LIBRARY/Rewritemap $OLD_LIBRARY/Rewritemap
deleteFiles $NEW_LIBRARY/Rewritemap $OLD_LIBRARY/Rewritemap

# Updating *Util* folder
updateFolder $NEW_LIBRARY/Util $OLD_LIBRARY/Util
deleteFiles $NEW_LIBRARY/Util $OLD_LIBRARY/Util

# Updating *View* folder
updateFolder $NEW_LIBRARY/View $OLD_LIBRARY/View
deleteFiles $NEW_LIBRARY/View $OLD_LIBRARY/View

echo "done"
