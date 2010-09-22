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
# @version     $Id: index_runner.sh 5765 2010-06-07 14:15:00Z claussni $
#

# run hourly on sdvqucosa-app00, sdvqucosa-app01

datum=`/bin/date '+%Y%m'`

php_bin=/usr/bin/php

index_runner='./index-runner.php'

stale_file='./index_cron.lock'

base_dir=

script_dir=${base_dir}/scripts/indexing

log_dir=${base_dir}/workspace/log
log_file=${log_dir}/cronjob-${datum}.log
#log_file=/dev/null

if [ ! -d ${script_dir} ] ; then
    echo 'not found index script directory.' >> ${log_file}
    exit 1;
fi

cd ${script_dir}

if [ -e ${stale_file} ] ; then
    echo 'one job already run.' >> ${log_file}
    exit 0;
fi

touch ${stale_file} 2>&1 >> ${log_file}

echo 'starting index job @' `/bin/date '+%Y%m%d %H:%M:%S'` 2>&1 >> ${log_file}

${php_bin} ${index_runner} 2>&1 >> ${log_file}

echo 'index job done @' `/bin/date '+%Y%m%d %H:%M:%S'` 2>&1 >> ${log_file}

rm ${stale_file} 2>&1 >> ${log_file}

