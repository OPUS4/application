#!/bin/sh

# clean files, everything will be imported newly

rm -rf ../workspace/files/*

cd ../tests

mv sql/999_create_documents_testdata.sql sql/999_create_documents_testdata.sql.disabled
mv sql/013_create_institutes_testdata.sql sql/013_create_institutes_testdata.sql.disabled

sh ./rebuilding_database.sh

cd ../scripts

php Opus3Migration.php
