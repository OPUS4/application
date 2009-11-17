#!/usr/bin/env sh
#
# This script is needed to make php forget session information between to requests.
# Also it makes the rewrite map more robust in case of php errors.

# please configure the next to lines
PHP='/usr/bin/php5'
MAP='/home/matheon/development/scripts/opus-apache-rewritemap.php'

THIS_UID="`/usr/bin/id -u`"
USER='www-data'

# FIXME: Decide which tool to choose.  "su" is more portable, but "sudo"
# FIXME: looks nicer in the process listing.
SU='/bin/su'
SUDO='/usr/bin/sudo'


# FIXME: Every user-id below 10 is "magic" and shouldn't be used.
# FIXME: Replace "magic" constant by something reasonable.
if test "0$THIS_UID" -le 10; then

   # exec $SU -c "$0" $USER
   exec $SUDO -u $USER "$0"

   # exec should never return, so this is just to prevent passing
   exit
fi


# DO NOT CHANGES ANYTHING BELLOW THIS LINE, EXCEPT YOU REALLY KNOW WHAT YOU ARE DOING!
# keep this quite simple!
while read request
do
    # FIXME: Don't start RewriteMap-Script for *every* matching request.  Just
    # FIXME: print one URL on STDOUT when a URL on STDIN arrives.  Forking PHP
    # FIXME: and bringing up the whole framework is way too expensive.
    echo `$PHP $MAP "$request"`
done
