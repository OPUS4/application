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
# @author      Jens Schwidder <schwidder@zib.de>
# @copyright   Copyright (c) 2011, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id$

# Updates workspace folder if necessary

set -o errexit;

source update-common.sh

setVars

# Remove workspace/files/error if present

FOLDER=$BASEDIR/workspace/files/error

DEBUG "Updating workspace"
DEBUG $FOLDER

if [[ -d $FOLDER ]]; then
    echo "Removing unused folder $FOLDER"
    deleteFolder "$FOLDER"
    echo "done"
fi

CACHE_DIR=$BASEDIR/workspace/cache

echo "Cleaning Zend Cache"
rm -f "$CACHE_DIR/zend_cache---*"
