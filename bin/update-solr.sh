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

# Update SOLR server

set -o errexit

source update-common.sh

setVars

OLD_SCRIPTS="$BASEDIR/opus4/scripts"
MD5PATH=solrconfig

# TODO Why check specifically for versions before 4.0.3? At least add comment.
if [[ -f "$BASE_SOURCE"/dbupdated.txt ]]; then
    echo "Updating SOLR server schema ..."
    # TODO use MD5Path? Not used in old script. 
    # What happens if schema.xml is remotely available?
    updateFile "$BASE_SOURCE/solrconfig" "$BASEDIR/solrconfig" "$MD5PATH" "schema.xml"
    echo "done"
    
    rm "$BASE_SOURCE"/dbupdated.txt
fi

