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
# @copyright   Copyright (c) 2010-2016, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License

#
# Adds testdata to database and rebuilds the index.
#
# TODO verify waiting for Solr works for local and remote setup
#

set -e

SCRIPT_NAME="`basename "$0"`"
SCRIPT_NAME_FULL="`readlink -f "$0"`"
SCRIPT_PATH="`dirname "$SCRIPT_NAME_FULL"`"

BASEDIR="`dirname "$SCRIPT_PATH"`"

#
# Prepare test workspace directories
#

mkdir -p "$BASEDIR/tests/workspace/files"
mkdir -p "$BASEDIR/tests/workspace/import"
mkdir -p "$BASEDIR/tests/workspace/incoming"
mkdir -p "$BASEDIR/tests/workspace/log"
mkdir -p "$BASEDIR/tests/workspace/cache"
mkdir -p "$BASEDIR/tests/workspace/export"
mkdir -p "$BASEDIR/tests/workspace/tmp"
mkdir -p "$BASEDIR/tests/workspace/tmp/resumption"

#
# Import test data into database
#
cd "$BASEDIR/tests"
php import-testdata.php

# copy test fulltexts to workspace directory
cd "$BASEDIR"

cp -rv tests/fulltexts/* workspace/files