#!/usr/bin/env bash

# TODO remove this file (compare function with set-file-permissions in bin folder)

set -e

FILEOWNER="$1"
SERVER_DIR="$2"


#
# Parameter checking.
#

if [[ -z $FILEOWNER ]] || [[ -z $SERVER_DIR ]]; then
   echo "usage: $0 <fileowner> <opus4 base directory>"
   exit 1;
fi

if [[ ! -d $SERVER_DIR ]] || [[ ! -d $SERVER_DIR/workspace/ ]]; then
   echo "Invalid/empty base directory '$SERVER_DIR' given."
   echo "base directory must non-empty and contain subfolder 'workspace'"
   exit 1
fi


#
# Fix permissions for server/workspace/cache/*
#

find "$SERVER_DIR/workspace/cache" -print0 |xargs -r0 chown "$FILEOWNER"
find "$SERVER_DIR/workspace/cache" -type d -print0 |xargs -r0 chmod 755
find "$SERVER_DIR/workspace/cache" -type f -print0 |xargs -r0 chmod 644


#
# Fix permissions recursively for server/workspace/files/*
#

find "$SERVER_DIR/workspace/files" -print0 |xargs -r0 chown "$FILEOWNER"
find "$SERVER_DIR/workspace/files" -type d -print0 |xargs -r0 chmod 755
find "$SERVER_DIR/workspace/files" -type f -print0 |xargs -r0 chmod 644


#
# Fix permissions for server/workspace/log/opus.log
#

chmod 777 "$SERVER_DIR/workspace/log/"
if [ -f "$SERVER_DIR/workspace/log/opus.log" ]; then
    chown "$FILEOWNER" "$SERVER_DIR/workspace/log/opus.log"
fi
