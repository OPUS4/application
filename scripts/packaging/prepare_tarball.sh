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
# @author      Thoralf Klein <thoralf.klein@zib.de>
# @copyright   Copyright (c) 2010, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id$

set -e

# Create opus-400-r6503.tgz from subversion-subdirectory tags/2010-10-08_4.00rc:
# ./prepare_tarball.sh tags/2010-10-08_4.00rc opus-400-r6503
#
# Create opus-trunk.tgz from subversion-subdirectory trunk:
# ./prepare_tarball.sh trunk opus-trunk

#
# Prepare directories
#

$(dirname $0)/prepare_directories.sh $1 $2

#
# Build tarball
#

cd $2
tar czvf ../$(basename $2).tgz opus4 workspace libs solrconfig
