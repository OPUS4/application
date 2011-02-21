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

PROJECTDIR=$1
SVNMODULES="application codingstandard server"
SVNDIRECTORIES="documentation experimental planning"

mkdir -vp $PROJECTDIR
cd $PROJECTDIR

for i in $SVNMODULES; do
    git svn clone --stdlayout https://svn.zib.de/opus4dev/$i $i
done

for i in $SVNDIRECTORIES; do
    git svn clone https://svn.zib.de/opus4dev/$i $i
done
