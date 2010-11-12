#!/bin/sh

# clean files, everything will be imported newly
cd ../workspace/files/
rm -rf *

# create clean database
cd ../../db
sh ./createdb.sh

# start Opus3-Migration
cd ../scripts
php Opus3Migration.php

