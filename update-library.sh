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

# TODO maybe script should take src and dest parameter?

set -o errexit

source update-common.sh

# TODO Move into common script? Be careful with main script!
BASEDIR=$1
BASE_SOURCE=$2

# Replace old framework folder with new one without checking for changes.
# TODO check folders

NEW_FRAMEWORK=$BASE_SOURCE/opus4/library/Opus
OLD_FRAMEWORK=$BASEDIR/library/Opus # TODO path correct?

DEBUG "NEW_FRAMEWORK = $NEW_FRAMEWORK"
DEBUG "OLD_FRAMEWORK = $OLD_FRAMEWORK"

echo -e "Updating *library* folder ... \c "
updateFolder $NEW_FRAMEWORK $OLD_FRAMEWORK
# TODO Are we possibly deleting too much in the next line? It will delete everything that does not exist in source location.
deleteFiles $NEW_FRAMEWORK $OLD_FRAMEWORK
# TODO verify with diff between $NEW_FRAMEWORK and $OLD_FRAMEWORK?
echo "done"
