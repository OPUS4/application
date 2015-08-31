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

# =============================================================================
# Updating DnbInstitute Data
# Splitting Name to Name and newly created Name and Department
# when updating from version < 4.4.1 to version >= 4.4.1
# =============================================================================


UPDATE_DNBINSTITUTES_LOG="$BASEDIR/UPDATE-dnbinstitutes.log"
echo "Updating DNB Institutes"
"$UPDATE_SCRIPT_DIR/update-dnbinstitutes.php" --host=$HOST --port=$PORT --dbname=$DBNAME --user=$USER --password=$PASSWORD >> "$UPDATE_DNBINSTITUTES_LOG"
