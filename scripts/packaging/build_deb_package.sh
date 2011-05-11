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
# @copyright   Copyright (c) 2010-2011, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id$


# usage:
# create opus_4.x.y_all.deb using temporary directory debtmp
# ./build_deb_package.sh debtmp [svn-tag] 

set -e

TEMPDIR="$1"
BASEDIR='/var/local/opus4'
TAG="$2"

if [ -z "$TEMPDIR" ]; then
  echo "argument missing"
  exit
fi

if [ -d "$TEMPDIR" ]; then
  echo "directory $TEMPDIR exists -- choose another one or delete it first"
  exit
fi

echo "create directory $TEMPDIR"
mkdir -vp "${TEMPDIR}${BASEDIR}"

svn --force export https://svn.zib.de/opus4dev/server/trunk/scripts/packaging/deb_package/DEBIAN/ "$TEMPDIR/DEBIAN"

if [ -z "$TAG" ]; then
  TAG='trunk'
fi
echo "get OPUS 4 source code (tag: $TAG)"
./prepare_directories.sh "$TAG" "${TEMPDIR}${BASEDIR}"

echo "create deb package"
md5sum `find "${TEMPDIR}${BASEDIR}" -type f` | sed -e "s/$TEMPDIR//" > "$TEMPDIR/DEBIAN/md5sums"
chmod +x "$TEMPDIR"/DEBIAN/{postinst,prerm}
dpkg-deb --build "$TEMPDIR" .

echo "remove directory $TEMPDIR"
rm -rf "$TEMPDIR"

