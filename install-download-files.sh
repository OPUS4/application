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


ZEND_LIB_URL='http://framework.zend.com/releases/ZendFramework-1.10.6/ZendFramework-1.10.6-minimal.tar.gz'
JPGRAPH_LIB_URL='http://jpgraph.net/download/download.php?p=1'
SOLR_SERVER_URL='http://www.apache.org/dist//lucene/solr/1.4.1/apache-solr-1.4.1.tgz'
SOLR_PHP_CLIENT_LIB_URL='http://solr-php-client.googlecode.com/svn/trunk/'
SOLR_PHP_CLIENT_LIB_REVISION='36'
JQUERY_LIB_URL='http://code.jquery.com/jquery-1.4.3.min.js'


if [ -d "$DOWNLOADS_DIR" ];
then
  echo "Download directory '$DOWNLOADS_DIR' already exists, exiting."
  exit 1;
fi

mkdir "$DOWNLOADS_DIR"
cd "$DOWNLOADS_DIR"


#
# downloading external dependencies
#

wget -O zend.tar.gz "$ZEND_LIB_URL"
if [ ! -f zend.tar.gz ]
then
  echo "Unable to download $ZEND_LIB_URL"
  exit 1
fi

wget -O jpgraph.tar.gz "$JPGRAPH_LIB_URL"
if [ ! -f jpgraph.tar.gz ]
then
  echo "Unable to download $JPGRAPH_LIB_URL"
  exit 1
fi

wget -O solr.tgz "$SOLR_SERVER_URL"
if [ ! -f solr.tgz ]
then
  echo "Unable to download $SOLR_SERVER_URL"
  exit 1
fi

svn export --revision "$SOLR_PHP_CLIENT_LIB_REVISION" --force "$SOLR_PHP_CLIENT_LIB_URL" "SolrPhpClient_r$SOLR_PHP_CLIENT_LIB_REVISION"
if [ ! -d "SolrPhpClient_r$SOLR_PHP_CLIENT_LIB_REVISION" ]
then
  echo "Unable to download $SOLR_PHP_CLIENT_LIB_URL"
  exit 1
fi

wget -O jquery.js "$JQUERY_LIB_URL"
if [ ! -f jquery.js ]
then
  echo "Unable to download $JQUERY_LIB_URL"
  exit 1
fi

exit 0
