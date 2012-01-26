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
# @author      Sascha Szott <szott@zib.de>
# @copyright   Copyright (c) 2012, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id$


# Migration of Collection-based Series (were eliminated in OPUS 4.2.0)

set -o errexit

source update-common.sh

setVars

DEBUG "BASEDIR = $BASEDIR"
DEBUG "BASE_SOURCE = $BASE_SOURCE"
DEBUG "MD5_OLD = $MD5_OLD"
DEBUG "VERSION_NEW = $VERSION_NEW"
DEBUG "VERSION_OLD = $VERSION_OLD"
DEBUG "_UPDATELOG = $_UPDATELOG"

# Ensure this is only done for updates from versions < 4.2.0
if [[ "$VERSION_OLD" < "4.2" && "$VERSION_NEW" > "4.2" ]]; then
   echo -e "Would you like to migrate your old series (y/N)? \c ";
   read ANSWER
   if [[ -z $ANSWER ]]; then
      ANSWER='n'
   else
      if [[ $ANSWER == 'y' ]]; then
         # inform user which series documents have no IdentifierSerial
         "$BASEDIR/opus4/scripts/update_migration/MigrateSeriesCollections.php" "$BASEDIR/UPDATE-series.log"
      else
         echo "Keep the old series."
      fi
   fi

   "$BASEDIR/opus4/scripts/update_migration/MigrateSubjectsToCollections.php" "$BASEDIR/UPDATE-subjects.log"
fi
