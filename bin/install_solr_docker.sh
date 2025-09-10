#!/usr/bin/env bash

ant download-solr -DsolrVersion=9.9.0 -DdownloadDir=./downloads
cd solr-9.9.0
./bin/solr start -force --user-managed
./bin/solr create -c opus4
cd server/solr/opus4/conf/
rm -f managed-schema schema.xml solrconfig.xml
ln -s ../../../../../vendor/opus4-repo/search/conf/schema.xml schema.xml
ln -s ../../../../../vendor/opus4-repo/search/conf/solrconfig.xml solrconfig.xml
cd ../../../../
./bin/solr restart -force
cd ..
