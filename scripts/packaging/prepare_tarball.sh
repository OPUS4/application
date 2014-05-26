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
# @author      Michael Lang <lang@zib.de>
# @copyright   Copyright (c) 2010-2014, OPUS 4 development team
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
# check for empty files
#
echo -e "\nempty files included in tarball:"
find $2 -size 0
echo

#
# write Version to application.ini
#
sed -i.bak "s/version = Opus.*/version = $2/g" $2/opus4/application/configs/application.ini

#
# Build tarball
#
echo "files included in tarball:"
filename=$(basename $2)
tar czvf "$filename".tgz "$filename"

#
# Compute MD5 and SHA512 hashes
#

#cd ..
md5sum $(basename $2).tgz > $(basename $2).md5
echo "created '$(basename $2).md5' -- do not forget to upload this file to Typo3 CMS"

sha512sum $(basename $2).tgz > $(basename $2).sha512
echo "created '$(basename $2).sha512' -- do not forget to upload this file to Typo3 CMS"

