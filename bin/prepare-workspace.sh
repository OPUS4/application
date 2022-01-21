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
# @author      Sascha Szott <szott@zib.de>
# @author      Jens Schwidder <schwidder@zib.de>
# @copyright   Copyright (c) 2010-2016, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License

#
# Creates folders for workspace.
#
# TODO add parameter for workspace location
#

set -e

SCRIPT_NAME="`basename "$0"`"
SCRIPT_NAME_FULL="`readlink -f "$0"`"
SCRIPT_PATH="`dirname "$SCRIPT_NAME_FULL"`"

BASEDIR="`dirname "$SCRIPT_PATH"`"

mkdir -p "$BASEDIR/workspace/filecache"
mkdir -p "$BASEDIR/workspace/files"
mkdir -p "$BASEDIR/workspace/import"
mkdir -p "$BASEDIR/workspace/incoming"
mkdir -p "$BASEDIR/workspace/log"
mkdir -p "$BASEDIR/workspace/cache"
mkdir -p "$BASEDIR/workspace/export"
mkdir -p "$BASEDIR/workspace/tmp"
mkdir -p "$BASEDIR/workspace/tmp/resumption"

