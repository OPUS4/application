#!/bin/sh

# clean files, everything will be imported newly

rm -rf ../workspace/files/*

# create clean database

sh ../db/createdb.sh

# start Opus3-Migration

php Opus3Migration.php
