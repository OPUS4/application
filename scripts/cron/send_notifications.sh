#! /bin/bash

# run every 10 minutes

datum=`/bin/date '+%Y%m'`

php_bin=/usr/bin/php

notification_runner='./publish_notification.php'

stale_file='./publish_notification_cron.lock'

base_dir=

script_dir=${base_dir}/scripts/indexing

log_dir=${base_dir}/workspace/log

log_file=${log_dir}/notification-cronjob-${datum}.log

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

echo 'starting publish notification job @' `/bin/date '+%Y%m%d %H:%M:%S'` 2>&1 >> ${log_file}

${php_bin} ${notification_runner} 2>&1 >> ${log_file}

echo 'publish notification job done @' `/bin/date '+%Y%m%d %H:%M:%S'` 2>&1 >> ${log_file}

rm ${stale_file} 2>&1 >> ${log_file}