#!/usr/bin/env bash

set -e

FILEOWNER="www-data:www-data"
DIR="$(pwd)"
SERVER_DIR="$(dirname $DIR)"

cd $SERVER_DIR/workspace

#
# Fix permissions for server/workspace/cache/*
#

if [ "$(ls cache)" ]; then
    chown $FILEOWNER cache/*
fi

#
# Fix permissions recursively for server/workspace/files/*
#

if [ "$(ls files)" ]; then
    chown -R $FILEOWNER files/[0-9]*
fi

#
# Fix permissions for server/workspace/log/opus.log
#

if [ -f log/opus.log ]; then
    chown $FILEOWNER log/opus.log
fi

cd $DIR

