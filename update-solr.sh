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
# @author      Susanne Gottwald <gottwald@zib.de>
# @author      Jens Schwidder <schwidder@zib.de>
# @copyright   Copyright (c) 2011, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id$

# Update SOLR server

if [ $VERSION_OLD==4.0.0 ] || [ $VERSION_OLD==4.0.1 ] || [ $VERSION_OLD==4.0.2 ]
then
	echo "The Solr server schema has to be updated."
	echo "*******************************************"
	DIR_O=$BASEDIR/solrconfig
	DIR_N=../solrconfig
	FILE=schema.xml
	filesDiff
	echo "The Solr index is rebuilding now..."
	php5 $OLD_SCRIPTS/SolrIndexBuilder.php
fi
echo ""

