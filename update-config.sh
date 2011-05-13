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

# Updates the OPUS4 configuration files

set -o errexit

# TODO move into common script? Be careful with main script.
BASEDIR=$1

source update-common.sh

DEBUG "BASEDIR = $BASEDIR"

OLD_CONFIG=$BASEDIR/opus4/application/configs
NEW_CONFIG1=opus4/application/configs # TODO rename variable MD5PATH ?
NEW_CONFIG=../$NEW_CONFIG1

echo "Updating configuration files ..."

cp $NEW_CONFIG/application.ini $OLD_CONFIG/application.ini
cp $NEW_CONFIG/config.ini.template $OLD_CONFIG/config.ini.template
cp $NEW_CONFIG/doctypes/all.xml $OLD_CONFIG/doctypes/all.xml

# TODO maybe config.ini should be merged with new template?

# DIR_O=$OLD_CONFIG # TODO remove
# DIR_N=$NEW_CONFIG # TODO remove
# MD5Path=$NEW_CONFIG1 # TODO remove

updateFile $NEW_CONFIG $OLD_CONFIG $NEW_CONFIG1 navigation.xml
updateFile $NEW_CONFIG $OLD_CONFIG $NEW_CONFIG1 navigationModules.xml


