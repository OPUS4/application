#!/bin/bash
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
# @version     $Id: remove_old_temp_files.sh 5765 2010-06-07 14:15:00Z claussni $
#

# run daily on sdvqucosa-file01

datum=`/bin/date '+%Y%m%d'`
tempPath=/srv/data/temp
maximumAge=7
touch_bin=/bin/touch
find_bin=/usr/bin/find

log_dir=${HOME}/log
if [ -d ${log_dir} ] ; then
    log_file=${log_dir}/remove-${datum}.log
else
    log_file=/dev/null
fi

cd ${tempPath}

${touch_bin} .htaccess >> ${log_file}

${find_bin} . -mtime +${maximumAge} -print -delete | sort  2>&1 >> ${log_file}

if [ ! -s ${log_file} -a ! -c ${log_file}] ; then
    rm -f ${log_file}
fi

