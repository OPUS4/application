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

#
# Update script for OPUS 4
#
# It may be necessary to run a composer update before running this script
# in case new or newer dependencies are required.
#
# php composer.phar update
#
# Normally it should be possible to run this script any time without harm.
# The database and the application have version numbers that are simple
# integer numbers and independent of the release versions, like "4.6".
#
# Every update step has a specific version number and once the steps has
# been performed the version of the installation is increased.
#

set -e

SCRIPT_NAME="`basename "$0"`"
SCRIPT_NAME_FULL="`readlink -f "$0"`"
SCRIPT_PATH="`dirname "$SCRIPT_NAME_FULL"`"

BASEDIR="`dirname "$SCRIPT_PATH"`"

# Perform update steps
php $BASEDIR/scripts/update/update.php

# TODO rebuild index ? maybe just notifice or use two cores
# TODO restart webserver ? just a notice