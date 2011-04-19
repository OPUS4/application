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
# @author      Sascha Szott <szott@zib.de>
# @copyright   Copyright (c) 2010-2011, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id$

set -e

TAG=$1
TEMPDIR=$2


mkdir -vp $TEMPDIR
cd $TEMPDIR
rm -rfv {opus4,solrconfig,libs,workspace,apacheconf,install,testdata}

#
# Checkout opus4-trunk
#

svn --force export https://svn.zib.de/opus4dev/server/$TAG opus4
svn --force export https://svn.zib.de/opus4dev/framework/$TAG/db/schema opus4/db/schema
svn --force export https://svn.zib.de/opus4dev/framework/$TAG/library/Opus opus4/library/Opus
svn --force export https://svn.zib.de/opus4dev/solrconfig/$TAG solrconfig
svn --force export https://svn.zib.de/opus4dev/apacheconf/$TAG apacheconf
svn --force export https://svn.zib.de/opus4dev/install/$TAG install

mkdir -pv testdata
cd testdata
svn --force export https://svn.zib.de/opus4dev/server/$TAG/tests/fulltexts fulltexts
svn --force export https://svn.zib.de/opus4dev/server/$TAG/tests/sql sql
cd ..

#
# Clean everything the user doesn't need
#

find -P . -name .gitignore -print0 | xargs -r0 rm -v 

# added checks to ensure compatibility with older releases
if [ -f opus4/scripts/packaging/changelog/CHANGES.txt ]; then
  mv opus4/scripts/packaging/changelog/CHANGES.txt .
else
  touch CHANGES.txt
fi
if [ -f opus4/scripts/packaging/gpl-3.0.txt ]; then
  mv opus4/scripts/packaging/gpl-3.0.txt .
else
  touch gpl-3.0.txt
fi
if [ -d opus4/scripts/packaging/releases ]; then
  mv opus4/scripts/packaging/releases .
else
  mkdir releases
fi;

rm -rv opus4/{docs,nbproject,tests,workspace}
rm -rvf opus4/public/layouts/{opus33,opus34,darker,matheon,plain,opus4-matheon}
rm -rv  opus4/import/importer/zib
rm -rv  opus4/import/stylesheets/zib
rm -v  opus4/modules/publish/views/scripts/form/preprintmatheon.phtml
rm -v  opus4/application/configs/doctypes/preprintmatheon.xml
rm -vf opus4/modules/publish/views/scripts/form/demo.phtml
rm -vf  opus4/application/configs/doctypes/demo.xml
rm -rv opus4/modules/{pkm,publicationList,remotecontrol,import}
rm -rv opus4/scripts/{packaging,cron,indexing,install}
rm -r  opus4/scripts/*Matheon*.php
rm -rv  opus4/scripts/zib
rm -rvf opus4/library/Opus/Search/{Adapter,Index}/Lucene/
rm -rvf testdata/sql/992_create_documents_testdata__security.sql
rm -rvf testdata/sql/990_create_documents_testdata__hhhar.sql


#
# Prepare libs/symlinks
#

mkdir -vp libs

ln -sv "../../libs/SolrPhpClient/Apache" "opus4/library/Apache"
ln -sv "../../libs/jpgraph/src" "opus4/library/jpgraph"
ln -sv "../../libs/ZendFramework/library/Zend" "opus4/library/Zend"

#
# Prepare workspace directory
#

mkdir -vp workspace/{cache,log,files,tmp,incoming}
mkdir -v workspace/tmp/resumption
ln -sv "../workspace" "opus4/workspace"

touch workspace/log/{opus.log,opus-console.log}
chmod 666 workspace/log/{opus.log,opus-console.log}
chmod 777 workspace/{files,cache,tmp,incoming}
chmod 777 workspace/tmp/resumption

#
# Make install/uninstall/update script executable
#

chmod +x install/install.sh
chmod +x install/uninstall.sh
chmod +x install/update.sh

#
# create VERSION.txt
#
if [ "$TAG" == "trunk" ]; then
  echo trunk > VERSION.txt
else
  echo ${TAG:16} > VERSION.txt
fi

#
# create MD5SUMS
#
find . -type f -not -name MD5SUMS -print0 | xargs -0 md5sum >> MD5SUMS

