#!/usr/bin/env bash
#
# This file is part of OPUS. The software OPUS has been originally developed
# at the University of Stuttgart with funding from the German Research Net,
# the Federal Department of Higher Education and Research and the Ministry
# of Science, Research and the Arts of the State of Baden-Wuerttemberg.
#
# OPUS 4 is a complete rewrite of the original OPUS software and was developed
# by the Stuttgart University Library, the Library Service Center
# Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
# the Cooperative Library Network Berlin-Brandenburg, the Saarland University
# and State Library, the Saxon State Library - Dresden State and University
# Library, the Bielefeld University Library and the University Library of
# Hamburg University of Technology with funding from the German Research
# Foundation and the European Regional Development Fund.
#
# LICENCE
# OPUS is free software; you can redistribute it and/or modify it under the
# terms of the GNU General Public License as published by the Free Software
# Foundation; either version 2 of the Licence, or any later version.
# OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
# WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
# details. You should have received a copy of the GNU General Public License 
# along with OPUS; if not, write to the Free Software Foundation, Inc., 51 
# Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
#
# @category    Application
# @author      Thoralf Klein <thoralf.klein@zib.de>
# @copyright   Copyright (c) 2011, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License

set -e

BASENAME="`which basename`"
DATE="`which date`"
PHP="`which php`"

if [ "0$#" -lt 2 ] ; then
   echo "USAGE: $0 PHP-SCRIPT LOCK-DIRECTORY [LOG-DIRECTORY]"
   exit 1;
fi

SCRIPT="$1"
LOCKDIR="$2"
LOGDIR="$3"

LOCKFILE="$LOCKDIR/`$BASENAME $SCRIPT`.lock"

LOGFILE=/dev/stdout
CRONLOG=/dev/stdout
if [ "-$LOGDIR-" != "--" ] && [ -d $LOGDIR ] ; then
  LOGFILE="$LOGDIR/`$DATE -I`-`$BASENAME $SCRIPT`.log"
  CRONLOG="$LOGDIR/crontab.log"
fi

if [ ! -e $LOCKDIR ] ; then
    echo "ERROR: Locking directory '$LOCKDIR' not found." 2>&1 >>$CRONLOG
    exit 1;
fi

if [ ! -e $SCRIPT ] ; then
    echo "ERROR: Requested script '$SCRIPT' not found." 2>&1 >>$CRONLOG
    exit 1;
fi

if [ -e $LOCKFILE ] ; then
    echo "ERROR: Requested script '$SCRIPT' already running or stale lock file '$LOCKFILE'." 2>&1 >>$CRONLOG
    exit 1;
fi

touch "$LOCKFILE" 2>&1 >>$CRONLOG
echo "`date --iso-8601=seconds`: starting job '$SCRIPT' ..." 2>&1 >>$CRONLOG

if $PHP $SCRIPT 2>&1 >>$LOGFILE; then
   echo "`date --iso-8601=seconds`: job '$SCRIPT' done." 2>&1 >>$CRONLOG
else
   echo "`date --iso-8601=seconds`: job '$SCRIPT' FAILED (logfile $LOGFILE, cronlog $CRONLOG)." 2>&1 |tee -a $CRONLOG
fi

rm "$LOCKFILE" 2>&1 >>$CRONLOG || :
