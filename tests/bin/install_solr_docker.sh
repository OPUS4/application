#!/usr/bin/env bash

#
# Script to install Solr. By default, version 9.4.0 will be installed.
# Another Solr version can be specified using the `--version` option.

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

# Check --version input
if ! [[ "$version" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
  echo "Unrecognized version number: $version"
  echo "The --version option requires a 3-digit version number, e.g.: 9.4.0"
  exit 1
fi

SOLR_VERSION="$version"
SOLR_TAR="solr-$SOLR_VERSION.tgz"

mkdir -p "downloads"
cd downloads

# Download Solr version
if test ! -f "$SOLR_TAR"; then
  # the Solr download URL differs for versions >=9.0.0
  if [[ "$version" =~ ^[1-8]\.[0-9]+\.[0-9]+$ ]]; then
    SOLR_URL="https://archive.apache.org/dist/lucene/solr/$SOLR_VERSION/$SOLR_TAR"
  elif [[ "$version" =~ ^(9|[1-9][0-9]+)\.[0-9]+\.[0-9]+$ ]]; then
    SOLR_URL="https://www.apache.org/dyn/closer.lua/solr/solr/$SOLR_VERSION/$SOLR_TAR?action=download"
  fi

  echo "Getting: $SOLR_URL"
  wget -q --show-progress --progress=bar:force $SOLR_URL -O $SOLR_TAR
fi

# Extract Solr archive
tar xfz "$SOLR_TAR" -C ..

# Configure & start Solr
cd ../solr-$SOLR_VERSION
./bin/solr start -force --user-managed
./bin/solr create -c opus4 -force
cd server/solr/opus4/conf/
rm -f managed-schema schema.xml solrconfig.xml
ln -s ../../../../../vendor/opus4-repo/search/conf/schema.xml schema.xml
ln -s ../../../../../vendor/opus4-repo/search/conf/solrconfig.xml solrconfig.xml
cd ../../../../
./bin/solr restart -force
cd ..
