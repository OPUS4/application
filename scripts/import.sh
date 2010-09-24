#!/bin/sh

mv ../application/configs/doctypes ../config/xmldoctypes.opus
mv ../application/configs/doctypes/import ../config/xmldoctypes

php Opus3Migration.php

mv ../config/xmldoctypes ../application/configs/doctypes/import
mv ../config/xmldoctypes.opus ../application/configs/doctypes