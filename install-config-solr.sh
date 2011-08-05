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
# @author      Thoralf Klein <thoralf.klein@zib.de>
# @copyright   Copyright (c) 2011, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id: install.sh 8791 2011-08-03 11:34:33Z tklein $

set -ex

CONFIG_INI="$1"
SOLR_INDEX_HOST="`echo "$2" |sed 's/\!/\\\!/g'`"
SOLR_INDEX_PORT="`echo "$3" |sed 's/\!/\\\!/g'`"
SOLR_INDEX_APP="`echo "$4" |sed 's/\!/\\\!/g'`"
SOLR_EXTRACT_HOST="`echo "$5" |sed 's/\!/\\\!/g'`"
SOLR_EXTRACT_PORT="`echo "$6" |sed 's/\!/\\\!/g'`"
SOLR_EXTRACT_APP="`echo "$7" |sed 's/\!/\\\!/g'`"

sed -e "s!^searchengine.index.host =!searchengine.index.host = '$SOLR_INDEX_HOST'!" \
    -e "s!^searchengine.index.port =!searchengine.index.port = '$SOLR_INDEX_PORT'!" \
    -e "s!^searchengine.index.app =!searchengine.index.app = '$SOLR_INDEX_APP'!" \
    -e "s!^searchengine.extract.host =!searchengine.extract.host = '$SOLR_EXTRACT_HOST'!" \
    -e "s!^searchengine.extract.port =!searchengine.extract.port = '$SOLR_EXTRACT_PORT'!" \
    -e "s!^searchengine.extract.app =!searchengine.extract.app = '$SOLR_EXTRACT_APP'!" \
    -i "$CONFIG_INI"

exit 0;
