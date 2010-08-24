!/usr/bin/env bash

set -ex

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
php SolrIndexBuilder.php