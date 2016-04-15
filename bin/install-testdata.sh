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
# @author      Jens Schwidder <schwidder@zib.de>
# @copyright   Copyright (c) 2010-2016, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License

#
# Adds testdata to database and rebuilds the index.
#
# TODO verify waiting for Solr works for local and remote setup
# TODO requires DB_ADMIN_PASSWORD DB_ADMIN DB_NAME (get from config.ini?)
#
#

set -e

SCRIPT_NAME="`basename "$0"`"
SCRIPT_NAME_FULL="`readlink -f "$0"`"
SCRIPT_PATH="`dirname "$SCRIPT_NAME_FULL"`"

BASEDIR="`dirname "$SCRIPT_PATH"`"

mysqlOpus4Admin() {
  "$MYSQL_CLIENT" --defaults-file=<(echo -e "[client]\npassword=${ADMIN_PASSWORD}") \
        --default-character-set=utf8 ${MYSQL_OPTS} -u "$ADMIN" -v $1
}

# import test data
cd "$BASEDIR"

for i in `find tests/sql -name *.sql \( -type f -o -type l \) | sort`; do
  echo "Inserting file '${i}'"
  mysqlOpus4Admin "$DBNAME" < "${i}"
done

# copy test fulltexts to workspace directory
cp -rv tests/fulltexts/* workspace/files

# TODO is waiting for running solr required since service script has been waiting for this before
# sleep some seconds to ensure the server is running
echo -en "\n\nwait until Solr server is running..."

waiting=true

pingSolr() {
  wget -SO- "$1" 2>&1
}

pingSolrStatus() {
  pingSolr "$1" | sed -ne 's/^ *HTTP\/1\.[01] \([0-9]\+\) .\+$/\1/p' | head -1
}

case "$SOLR_MAJOR" in
  5)
    PING_URL="http://localhost:${SOLR_SERVER_PORT}${SOLR_CONTEXT}/admin/ping"
    ;;
  *)
    PING_URL="http://localhost:${SOLR_SERVER_PORT}${SOLR_CONTEXT}/admin/ping"
esac

while $waiting; do
  echo -n "."
  state=$(pingSolrStatus "$PING_URL")
  case $state in
    200|304)
      waiting=false
      ;;
    500)
      echo -e "\n\nSolr server responds on error:\n" >&2
      pingSolr "$PING_URL" >&2
      exit 1
      ;;
    *)
      sleep 2
  esac
done

echo "completed."
echo -e "Solr server is running under http://localhost:$SOLR_SERVER_PORT/solr\n"

# start indexing of testdata
"$BASEDIR/scripts/SolrIndexBuilder.php"
