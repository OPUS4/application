#!/usr/bin/env bash

# Used for demo instance
# TODO should be move to a opus4-demo package

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
# Rebuild database and index.
#

cd $INSTANCE_DIR/server/tests
./rebuilding_database.sh

# remove all fulltext associated with hhar test documents
php bin/opus4 console:exec demo/delete_files.php

cd $INSTANCE_DIR

php bin/opus4 index:index