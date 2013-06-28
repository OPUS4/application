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
svn --force export https://svn.zib.de/opus4dev/documentation/$TAG/help_and_manual/OPUS4_Handbuch/opus_dokumentation_de.pdf

mkdir -pv testdata
cd testdata
svn --force export https://svn.zib.de/opus4dev/server/$TAG/tests/fulltexts fulltexts
svn --force export https://svn.zib.de/opus4dev/server/$TAG/tests/sql sql
cd ..

#
# Clean everything the user doesn't need
#

find -P . -name .gitignore -print0 | xargs -r0 rm -v

# added checks for existence to ensure compatibility with older releases

if [ -f opus4/scripts/packaging/changelog/CHANGES.txt ]; then
  mv opus4/scripts/packaging/changelog/CHANGES.txt .
else
  echo "WARN: CHANGES.txt does not exist\n"
  touch CHANGES.txt
fi

if [ -f opus4/scripts/packaging/gpl-3.0.txt ]; then
  mv opus4/scripts/packaging/gpl-3.0.txt .
else
  echo "WARN: gpl-3.0.txt does not exist\n"
  touch gpl-3.0.txt
fi

if [ -f opus4/scripts/packaging/releasenotes/RELEASE_NOTES.txt ]; then
  mv opus4/scripts/packaging/releasenotes/RELEASE_NOTES.txt .
else
  echo "WARN: RELEASE_NOTES.txt does not exist\n"
  touch RELEASE_NOTES.txt
fi

if [ -d opus4/scripts/packaging/releases ]; then
  mv opus4/scripts/packaging/releases .
else
  echo "WARN: directory 'releases' does not exist\n"
  mkdir releases
fi


#
# exclude files and directories
#

rm -rv opus4/{nbproject,tests,workspace}
rm -rvf opus4/public/layouts/{opus33,opus34,darker,matheon,plain,opus4-matheon}

# exclude XML-definitions for test document types
rm -v opus4/application/configs/doctypes/preprintmatheon.xml
rm -v opus4/application/configs/doctypes/demo.xml
rm -v opus4/application/configs/doctypes/collections.xml
rm -v opus4/application/configs/doctypes/talkzib.xml
rm -v opus4/application/configs/doctypes/demo_invalid.xml
rm -v opus4/application/configs/doctypes/demo_invalidfieldname.xml
rm -v opus4/application/configs/doctypes/demodemo.xml
rm -v opus4/application/configs/doctypes/single_level_collection.xml
rm -v opus4/application/configs/doctypes/foobar.xml

# exclude PHTML-templates for test document types
rm -v opus4/application/configs/doctypes_templates/preprintmatheon.phtml
rm -v opus4/application/configs/doctypes_templates/demo.phtml
rm -v opus4/application/configs/doctypes_templates/collections.phtml
rm -v opus4/application/configs/doctypes_templates/talkzib.phtml
rm -v opus4/application/configs/doctypes_templates/demodemo.phtml
rm -v opus4/application/configs/doctypes_templates/single_level_collection.phtml
rm -v opus4/application/configs/doctypes_templates/barfoo.phtml

# exclude modules
rm -rv opus4/modules/{remotecontrol,matheon}

# exclude scripts
rm -rv opus4/scripts/{packaging,indexing,install}
rm -rv opus4/scripts/cron/cron-send-review-request.php

# exclude framework classes
rm -rvf opus4/library/Opus/Search/{Adapter,Index}/Lucene/

# exclude certain testdata
rm -rvf testdata/sql/992_create_documents_testdata__security.sql
rm -rvf testdata/sql/990_create_documents_testdata__hhhar.sql

# exclude CSV metadata import script (see OPUSVIER-2497)
rm -v opus4/scripts/import/CSVImporter.php

# exclude MARCXML import XSL stylesheet (see OPUSVIER-2581)
rm -v opus4/scripts/import/marcxml-import.xsl

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

mkdir -vp workspace/{cache,log,files,tmp,incoming,export}
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
chmod +x install/update*.sh
chmod +x install/opus4-solr-jetty

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

