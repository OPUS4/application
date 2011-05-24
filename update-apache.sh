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

# Update Apache configuration
# The Apache configuration for OPUS4 is located in $BASEDIR/apacheconf/opus4
# and linked into /etc/apache2/sites-available.
# The file might have been modified locally. The user should decide what should
# happen in that case.

set -o errexit

BASEDIR="$1"
BASE_SOURCE="$2"
MD5_OLD="$3"

source update-common.sh

MD5PATH=apacheconf

echo -e "Updating Apache configuration ... \c "
# TODO updateFile either replaces or does not replace file, should create backup
# TODO check if file has been modified, if yes set restart flag for APACHE
updateFile "$BASE_SOURCE/$MD5PATH" "$BASEDIR/$MD5PATH" "$MD5PATH" "opus4"
echo "done"