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
# @copyright   Copyright (c) 2012, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id$

set -e

VERSION=trunk
TEMPDIR="$(date --iso-8601=seconds)-doku-tree-opus4-$VERSION"

./prepare_tarball.sh "$VERSION" "$TEMPDIR"
tree -L 3 -F --dirsfirst --charset ascii "$TEMPDIR" >"$TEMPDIR.tree"

rm -rv "$TEMPDIR/"
rm -rv "$TEMPDIR.tgz"
