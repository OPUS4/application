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

TAG=$1
TEMPDIR=$2


mkdir -vp $TEMPDIR
cd $TEMPDIR


#
# Checkout opus4-trunk
#

svn export https://svn.zib.de/opus4dev/server/$TAG opus4
svn export https://svn.zib.de/opus4dev/framework/$TAG/db/schema opus4/db/schema
svn export https://svn.zib.de/opus4dev/framework/$TAG/library/Opus opus4/library/Opus
svn export https://svn.zib.de/opus4dev/solrconfig solrconfig


#
# Clean everything the user doesn't need
#

find -P . -name .gitignore -print0 | xargs -r0 rm -v 

rm -rv opus4/{docs,nbproject,tests,workspace}
rm -rv opus4/public/layouts/{opus33,opus34,darker,matheon}
rm -rv opus4/import
rm -v  opus4/modules/publish/views/scripts/form/preprintmatheon.phtml
rm -rv opus4/modules/{pkm,publicationList,remotecontrol}
rm -rv opus4/scripts/{packaging,cron,indexing,install}
rm -r  opus4/scripts/*{Matheon,ZIB}*.php
rm -rvf opus4/library/Opus/Search/{Adapter,Index}/Lucene/


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

mkdir -vp workspace/{cache,logs,files,tmp}
ln -sv "../workspace" "opus4/workspace"

touch workspace/logs/opus.log
chmod 666 workspace/logs/opus.log
chmod 777 workspace/{files,cache,tmp}
