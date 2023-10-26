#!/usr/bin/env bash

#
# Script to install Solr. By default, version 9.4.0 will be installed.
# Another Solr version can be specified using the `--version` option.

script_name="$(basename "$0")"

# Define variables and their default values
version="9.4.0"

# Parse command line options
while [ $# -gt 0 ]; do
    if [[ $1 == "--"* ]]; then # only deal with long options
        if [[ -n "$2" && $2 != "-"* ]]; then # ignore options without a value
            # Create variable name from option name
            v="${1/--/}" # uses parameter expansion removing '--'

            # Read option value into variable
            declare "$v"="$2"

            # Process next option
            shift
        fi
    fi
    shift
done

SOLR_VERSION="$version"

# Download Solr version
if [[ "$version" =~ ^[1-8]\.[0-9]+\.[0-9]+$ ]]; then
  SOLR_URL="https://archive.apache.org/dist/lucene/solr/${solrVersion}/solr-${solrVersion}.tgz"
elif [[ "$version" =~ ^(9|[1-9][0-9]+)\.[0-9]+\.[0-9]+$ ]]; then # new archive URL for versions >9.0.0
  SOLR_URL="https://www.apache.org/dyn/closer.lua/solr/solr/$SOLR_VERSION/solr-$SOLR_VERSION.tgz?action=download"
else
  echo "Unrecognized version number"
  echo -e "The --version option requires a 3-digit version number, e.g.: 9.4.0"
  exit 1
fi

echo "Getting: $SOLR_URL"
wget -q $SOLR_URL -O - | tar -xz

# Configure & start Solr
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
