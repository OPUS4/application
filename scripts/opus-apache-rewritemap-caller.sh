#!/usr/bin/env sh
#
# This script is needed to make php forget session information between to requests.
# Also it makes the rewrite map more robust in case of php errors.

# please configure the next to lines
PHP='/usr/bin/php5'
MAP='/home/developer/workspace/bsz_opus_application/scripts/opus-apache-rewritemap.php'

# DO NOT CHANGES ANYTHING BELLOW THIS LINE, EXCEPT YOU REALLY KNOW WHAT YOU ARE DOING!
# keep this quite simple!
while read request
do
    echo `$PHP $MAP "$request"`
done