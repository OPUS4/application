#! /bin/bash

set -e

migration_dir=$(cd `dirname $0` && pwd)
# path to directory thats hosts migration log files
migration_log_dir=$migration_dir/log
# locking file for migration process
migration_lock_file=$migration_log_dir/migration.lock
# path to directory that hosts temporary migration files
migration_tmp_dir=$migration_dir/tmp
# path to script directory
script_dir=$migration_dir/..
# path to workspace directory
workspace_dir=$script_dir/../workspace
# path to workspace directory that hosts fulltext files
workspace_files_dir=$workspace_dir/files
# path to workspace directory that hosts cached files
workspace_cache_dir=$workspace_dir/cache
# path to db directory
db_dir=$script_dir/../db
# path to config directory
config_dir=$script_dir/../application/configs 
# path to migration.ini
migration_ini=$config_dir/migration.ini
# path to migration_config.ini 
migration_config_ini=$config_dir/migration_config.ini

[ ! -f "$migration_ini" -o ! -r "$migration_ini" ] && echo "Aborting migration: Configurationfile '`readlink -f $migration_ini`' does not exist or is not readable." && exit -1

[ ! -f "$migration_config_ini" -o ! -r "$migration_config_ini" ] && echo "Aborting migration: Configurationfile '`readlink -f $migration_config_ini`' does not exist or is not readable." && exit -1

echo "Remove migration/log/*.log and migration/tmp/*.map"
[ ! -d "$migration_log_dir" ] && mkdir "$migration_log_dir"
[ ! -d "$migration_tmp_dir" ] && mkdir "$migration_tmp_dir"

[ ! -r "$migration_log_dir" -o ! -w "$migration_log_dir" ] && echo "Aborting migration: Migration-Log-Dir '$migration_log_dir' is not readable or writeable." && exit -1
[ ! -r "$migration_tmp_dir" -o ! -w "$migration_tmp_dir" ] && echo "Aborting migration: Migration-Tmp-Dir '$migration_tmp_dir' is not readable or writeable." && exit -1

find "$migration_log_dir" -maxdepth 1 -type f -name *.log -exec rm {} \;
find "$migration_tmp_dir" -maxdepth 1 -type f -name *.map -exec rm {} \;

echo "Remove workspace/cache/zend_cache* and workspace/files/[0-9]*"
find "$workspace_cache_dir" -maxdepth 1 -type f -name zend_cache* -exec rm {} \;
find "$workspace_files_dir" -maxdepth 1 -type d -name [0-9]* -exec rm -r {} \;

echo "Clean database"
cd "$db_dir"
./createdb.sh || { echo "Aborting migration: creatdb.sh FAILED."; exit -1; }

