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
# @author      Edouard Simon <edouard.simon@zib.de>
# @copyright   Copyright (c) 2014, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id: update-db.sh 12418 2013-08-16 12:30:18Z esimon $


BASEDIR=$1;

SCRIPT_PATH="$BASEDIR/scripts/update/fix-collections-sortorder.php"

[[ -f "$SCRIPT_PATH" ]] && php "$SCRIPT_PATH" || echo "Could not find update script $SCRIPT_PATH" && exit 1
