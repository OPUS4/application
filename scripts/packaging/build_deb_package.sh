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
# @copyright   Copyright (c) 2010, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id$

set -e

rm -rf deb_package/var/local/opus4

echo "create directory deb_package/var/local/opus4"
mkdir -p deb_package/var/local/opus4

echo "get OPUS 4 source code"
./prepare_directories.sh trunk deb_package/var/local/opus4

echo "create deb package"
chmod +x deb_package/DEBIAN/postinst
dpkg-deb --build deb_package .
