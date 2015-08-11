#!/usr/bin/env bash
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
# @author      Thoralf Klein <thoralf.klein@zib.de>
# @copyright   Copyright (c) 2010-2011, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id$

set -e

SCRIPT_NAME=`basename "$0"`
DOWNLOADS_DIR="$1"


ZEND_LIB_URL='https://packages.zendframework.com/releases/ZendFramework-1.12.9/ZendFramework-1.12.9-minimal.tar.gz'
JPGRAPH_LIB_URL='http://jpgraph.net/download/download.php?p=1'
SOLR_SERVER_URL='http://archive.apache.org/dist/lucene/solr/5.2.1/solr-5.2.1.tgz'
JQUERY_LIB_URL='http://code.jquery.com/jquery-1.4.3.min.js'


mkdir -p "$DOWNLOADS_DIR"
cd "$DOWNLOADS_DIR"


#
# downloading external dependencies
#

if [ ! -f zend.tar.gz ]; then
  wget -O zend.tar.gz "$ZEND_LIB_URL"
  if [ $? -ne 0 -o ! -f zend.tar.gz ]
  then
    echo "Unable to download $ZEND_LIB_URL"
    exit 1
  fi
fi

if [ ! -f jpgraph.tar.gz ]; then
  wget -O jpgraph.tar.gz "$JPGRAPH_LIB_URL"
  if [ $? -ne 0 -o ! -f jpgraph.tar.gz ]
  then
    echo "Unable to download $JPGRAPH_LIB_URL"
    exit 1
  fi
fi

if [ ! -f solr.tar.gz ]; then
  wget -O solr.tar.gz "$SOLR_SERVER_URL"
  if [ $? -ne 0 -o ! -f solr.tar.gz ]
  then
    echo "Unable to download $SOLR_SERVER_URL"
    exit 1
  fi
fi

if [ ! -f jquery.js ]; then
  wget -O jquery.js "$JQUERY_LIB_URL"
  if [ ! -f jquery.js ]
  then
    echo "Unable to download $JQUERY_LIB_URL"
    exit 1
  fi
fi

exit 0
