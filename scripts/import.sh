#!/bin/sh

mv ../config/xmldoctypes ../config/xmldoctypes.opus
mv ../config/xmldoctypes.import ../config/xmldoctypes

php Opus3Migration.php

mv ../config/xmldoctypes ../config/xmldoctypes.import
mv ../config/xmldoctypes.opus ../config/xmldoctypes