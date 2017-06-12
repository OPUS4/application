#!/usr/bin/env bash
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
# @copyright   Copyright (c) 2010-2017, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
#

set -e

SCRIPT_NAME="`basename "$0"`"
SCRIPT_NAME_FULL="`readlink -f "$0"`"
SCRIPT_PATH="`dirname "$SCRIPT_NAME_FULL"`"

BASEDIR="`dirname "$SCRIPT_PATH"`"

# TODO update composer ?

# Perform update steps
php $BASEDIR/scripts/update/update.php

# TODO clean cache ? In order to refresh translations.

# TODO rebuild index ?

# TODO restart webserver ?