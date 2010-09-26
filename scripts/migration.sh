#!/bin/sh

# clean files, everything will be imported newly

rm -rf ../workspace/files/*

cd ../tests

mv ../application/configs/doctypes ../config/xmldoctypes.opus
mv ../application/configs/doctypes/import ../config/xmldoctypes

mv sql/999_create_documents_testdata.sql sql/999_create_documents_testdata.sql.disabled
mv sql/013_create_institutes_testdata.sql sql/013_create_institutes_testdata.sql.disabled

sh ./rebuilding_database.sh

cd ../scripts

php Opus3Migration.php

mv ../config/xmldoctypes ../application/configs/doctypes/import
mv ../config/xmldoctypes.opus ../application/configs/doctypes

