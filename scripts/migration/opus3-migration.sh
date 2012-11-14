#! /bin/bash

set -e

##
## Call This Skript with paramaters:
## -f OPUS3-XML-database export file (e.g. /usr/local/opus/complete_database.xml)
## -p Path your OPUS3 fulltext files (e.g. /usr/local/opus/htdocs/volltexte)
## -z Stepsize for looping
## -n No iteration after first looping
## -i Build Index after each loop
## -t Testing
##

stepsize=50
iteration=1
buildindex=0
testing=0

while getopts f:p:z:int o
do	case "$o" in
	f)	xmlfile="$OPTARG";;
	p)	fulltextpath="$OPTARG";;
        z)	stepsize="$OPTARG";;
        i)      buildindex=1;;
        n)      iteration=0;;
        t)      testing=1;;
	[?])	print "Usage: $0 [-f xmlfile] [-p fulltextpath] [-z stepsize for looping] [-i ] [ -n ]"
		exit 1;;
	esac
done

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

[ ! -f "$xmlfile" -o ! -r "$xmlfile" ] && echo "Aborting migration: Opus3-XML-Dumpfile '$xmlfile' does not exist or is not readable." && exit -1
xml_file="$(readlink -f "$xmlfile")"

[ ! -d "$fulltextpath" -o ! -r "$fulltextpath" ] && echo "Aborting migration: Opus3-Fulltextpath '$fulltextpath' does not exist or is not readable." && exit -1
fulltext_path="$(readlink -f "$fulltextpath")"

[ -z "${stepsize##*[!0-9]*}" ] && echo "Aborting migration: Stepsize '$stepsize' is not a valid number." && exit -1

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

echo "Validation of Opus3-XML-Dumpfile"
cd "$migration_dir"
php Opus3Migration_Validation.php -f "$xml_file" || { echo "Aborting migration: Opus3Migration_Validation.php FAILED"; exit -1; }

echo "Validation of Consistency of Opus3-XML-Dumpfile"
cd "$migration_dir"
check=`php ../xslt.php stylesheets/check.xslt "$xml_file"`
[[ -z $check ]] || { echo "Aborting migration: Consistency check of Opus3-Dump FAILED"; echo $check; exit -1; }

echo "Import institutes, collections and licenses"
php Opus3Migration_ICL.php -f "$xml_file" || { echo "Aborting migration: Opus3Migration_ICL.php FAILED"; exit -1; }

echo "Import metadata and fulltext"
start=1
end=`expr $start + $stepsize - 1`

APPLICATION_ENV=production;
[ "$testing" -eq "1" ] && APPLICATION_ENV=testing;
export APPLICATION_ENV;

touch "$migration_lock_file"
php Opus3Migration_Documents.php -f "$xml_file" -p "$fulltext_path" -s $start -e $end -l "$migration_lock_file" || { echo "Aborting migration: Opus3Migration_Documents.php FAILED"; exit -1; }

while [ -f "$migration_lock_file" ] && [ "$iteration" -eq "1" ]
do
    start=`expr $start + $stepsize`
    end=`expr $end + $stepsize`
    if [ "$buildindex" -eq "1" ]
    then
        cd "$script_dir"
        php SolrIndexBuilder.php || { echo "Aborting migration: SolrIndexBuilder.php  FAILED"; exit -1; }
        cd "$migration_dir"
    fi
    php Opus3Migration_Documents.php -f "$xml_file" -p "$fulltext_path" -s $start -e $end -l "$migration_lock_file" || { echo "Aborting migration: Opus3Migration_Documents.php FAILED"; exit -1; }
done

cd "$script_dir"
php SolrIndexBuilder.php || { echo "Aborting migration: SolrIndexBuilder.php  FAILED"; exit -1; }
cd "$migration_dir"
