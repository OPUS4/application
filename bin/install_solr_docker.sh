#!/usr/bin/env bash

#
# Script to install Solr. By default, version 9.3.0 will be installed.
# Another Solr version can be specified using the `--version` option.

script_name="$(basename "$0")"

# Define variables and their default values
version="9.3.0"

# Define colors
DEFAULT='\033[0;33m' # Purple color for default values
OPTION='\033[1;32m'  # Green
NC='\033[0m'         # No Color

# Print command line help to stderr
displayHelp() {
  echo "Usage: $script_name [OPTIONS]" >&2
  echo
  echo -e "This script will install Solr version 9.3.0, unless"
  echo -e "option ${OPTION}--version${NC} is specified with another Solr version."
  echo
  echo "Options:"
  echo
  echo -e "  ${OPTION}--help${NC}        (${OPTION}-h${NC})    Print out help"
  echo
  echo -e "                                          DEFAULT"
  echo -e "  ${OPTION}--version${NC}             Solr version      (${DEFAULT}$version${NC})"
  echo
  echo "Examples:"
  echo
  echo -e "  $script_name"
  echo -e "  $script_name ${OPTION}--help${NC}"
  echo -e "  $script_name ${OPTION}--version${NC} ${DEFAULT}7.7.2${NC}"
  echo
  exit 1
}

# Display command line help if '-h' or '--help' is given as first option
if [ $# -gt 0 ]; then
    if [[ $1 == "-h" || $1 == "--help" ]]; then
        displayHelp
        exit 0
    fi
fi

# Parse any other command line options
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

if [[ "$version" =~ ^[1-8]\.[0-9]+\.[0-9]+$ ]]; then
  SOLR_VERSION="$version"
  ant download-solr -DsolrVersion=$SOLR_VERSION -DdownloadDir=./downloads
elif [[ "$version" =~ ^(9|[1-9][0-9]+)\.[0-9]+\.[0-9]+$ ]]; then
  SOLR_VERSION="$version"
  wget -q "https://archive.apache.org/dist/solr/solr/$SOLR_VERSION/solr-$SOLR_VERSION.tgz" -O - | tar -xz
else
  echo "Unrecognized version number"
  echo -e "The ${OPTION}--version${NC} option requires a 3-digit version number, e.g.: 9.3.0"
  exit 1
fi

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
