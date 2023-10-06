#!/usr/bin/env bash

SOLR_VERSION="9.3.0"
wget -q "https://dlcdn.apache.org/solr/solr/$SOLR_VERSION/solr-$SOLR_VERSION.tgz" -O - | tar -xz
cd solr-$SOLR_VERSION
./bin/solr start -force
./bin/solr create -c opus4 -force
cd server/solr/opus4/conf/
rm -f managed-schema schema.xml solrconfig.xml
ln -s ../../../../../vendor/opus4-repo/search/conf/schema.xml schema.xml
ln -s ../../../../../vendor/opus4-repo/search/conf/solrconfig.xml solrconfig.xml
cd ../../../../
./bin/solr restart -force
cd ..
