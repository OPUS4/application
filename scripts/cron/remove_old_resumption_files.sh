#! /bin/bash
#
# LICENCE
# This code is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This code is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# @author      Henning Gerhardt <henning.gerhardt@slub-dresden.de>
# @copyright   Copyright (c) 2009-2010
#              Saechsische Landesbibliothek - Staats- und Universitaetsbibliothek Dresden (SLUB)
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id$
#

# run daily on sdvqucosa-app02, sdvqucosa-app03

workspace=/srv/workspace
resumptionPath=${workspace}/tmp/resumption

datum=`date +%Y%m%d`
maximumAge=1

logdir=${HOME}/log

if [ -d ${logdir} ] ; then
    logfile=${logdir}/remove-${datum}.log
    cronlog=${logdir}/crontab.log
else
    logfile=/dev/null
    cronlog=/dev/null
fi

if [ ! -d ${resumptionPath} ] ; then
    echo 'Directory with resumption files does not exists.' 2>&1 >> ${cronlog}
    exit 1;
fi

cd ${resumptionPath}

find . -mtime +${maximumAge} -print -delete | sort 2>&1 >> ${logfile}

if [ ! -s ${logfile} -a ! -c ${logfile} ] ; then
    echo 'Removing todays empty logfile ' ${logfile} 2>&1 >> ${cronlog}
    rm -f ${logfile} 2>&1 >> ${cronlog}
fi

