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

BASEDIR=$1

source update-common.sh

OLD_SCRIPTS=$BASEDIR/opus4/scripts
NEW_SCRIPTS1=opus4/scripts
NEW_SCRIPTS=../$NEW_SCRIPTS1

echo -e "Updating $OLD_SCRIPTS ... \c "
# Files in the scripts folder are updated without checks.
updateFolder $NEW_SCRIPTS $OLD_SCRIPTS
deleteFiles $NEW_SCRIPTS $OLD_SCRIPTS
echo "done"

