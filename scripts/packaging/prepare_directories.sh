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
# @copyright   Copyright (c) 2010, OPUS 4 development team
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
svn --force export https://svn.zib.de/opus4dev/solrconfig solrconfig
svn --force export https://svn.zib.de/opus4dev/apacheconf apacheconf
svn --force export https://svn.zib.de/opus4dev/install install

mkdir -pv testdata
cd testdata
svn --force export https://svn.zib.de/opus4dev/server/$TAG/tests/fulltexts fulltexts
svn --force export https://svn.zib.de/opus4dev/server/$TAG/tests/sql sql
cd ..

#
# Clean everything the user doesn't need
#

find -P . -name .gitignore -print0 | xargs -r0 rm -v 

mv opus4/scripts/packaging/changelog/CHANGES.txt .

rm -rv opus4/{docs,nbproject,tests,workspace}
rm -rvf opus4/public/layouts/{opus33,opus34,darker,matheon,plain,opus4-matheon}
rm -r  opus4/import/importer/ZIB*.php
rm -r  opus4/import/stylesheets/zib*.xslt
rm -v  opus4/modules/publish/views/scripts/form/preprintmatheon.phtml
rm -v  opus4/application/configs/doctypes/preprintmatheon.xml
rm -rv opus4/modules/{pkm,publicationList,remotecontrol}
rm -rv opus4/scripts/{packaging,cron,indexing,install}
rm -r  opus4/scripts/*{Matheon,ZIB}*.php
rm -r  opus4/scripts/*{Base,Parameters,Readline}*.php
rm -r  opus4/scripts/Opus3Migration.php
rm -r  opus4/scripts/migration.sh
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

mkdir -vp workspace/{cache,log,files,tmp}
mkdir -v workspace/tmp/resumption
ln -sv "../workspace" "opus4/workspace"

touch workspace/log/opus.log
chmod 666 workspace/log/opus.log
chmod 777 workspace/{files,cache,tmp}
chmod 777 workspace/tmp/resumption
