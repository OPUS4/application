#!/usr/bin/env bash

# TODO move this file to another place (it is used for demo instance)

# set -ex
set -e

INSTANCE="$1"
echo "updating instance: $INSTANCE"

cd $INSTANCE
INSTANCE_DIR="$(pwd)"

#
# Update sources from Subversion.
#

for i in server framework; do
   cd $INSTANCE_DIR/$i
   svn up
done

#
# Clean directories.
#

rm -f $INSTANCE/server/workspace/cache/zend*

#
# Run unit tests
#

# cd $INSTANCE_DIR/framework/tests
# phpunit --verbose

#
# Rebuilde database and index.
#

cd $INSTANCE_DIR/server/tests
./rebuilding_database.sh

cd $INSTANCE_DIR/server/scripts

# remove all fulltext associated with hhar test documents
php opus-console.php snippets/delete_files.php

cd $INSTANCE_DIR

php bin/opus4 index:index