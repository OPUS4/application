#! /bin/bash

set -e

##
## Call This Skript with paramaters:
## -f OPUS3-XML-database export file (e.g. /usr/local/opus/complete_database.xml)
## -p Path your OPUS3 fulltext files (e.g. /usr/local/opus/htdocs/volltexte)
## -z Stepsize for looping
## -n No iteration after first looping
## -i Build Index after each loop
##

stepsize=50
iteration=1

while getopts f:p:z:in o
do	case "$o" in
	f)	xmlfile="$OPTARG";;
	p)	fulltextpath="$OPTARG";;
        z)	stepsize="$OPTARG";;
        i)      buildindex=1;;
        n)      iteration=0;;
	[?])	print "Usage: $0 [-f xmlfile] [-p fulltextpath] [-z stepsize for looping] [-i ] [ -n ]"
		exit 1;;
	esac
done

migration_dir=$(cd `dirname $0` && pwd)
# path to directory thats hosts migration log files
migration_log_dir=$migration_dir/log
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
xml_file=$(readlink -f $xmlfile)
#xmllint --noout "$xml_file"
#if [ "$?" -ne "0" ]; then
#    echo "given Opus3-XML-Dumpfile is not well-formed"
#    exit
#fi

[ ! -d "$fulltextpath" -o ! -r "$fulltextpath" ] && echo "Aborting migration: Opus3-Fulltextpath '$fulltextpath' does not exist or is not readable." && exit -1
fulltext_path=$(readlink -f $fulltextpath)

[ -z "${stepsize##*[!0-9]*}" ] && echo "Aborting migration: Stepsize '$stepsize' is not a valid number." && exit -1

echo "Remove migration/log/* and migration/tmp/*"
[ ! -d "$migration_log_dir" ] && mkdir $migration_log_dir
[ ! -d "$migration_tmp_dir" ] && mkdir $migration_tmp_dir

[ ! -r "$migration_log_dir" -o ! -w "$migration_log_dir" ] && echo "Aborting migration: Migration-Log-Dir '$migration_log_dir' is not readable or writeable." && exit -1
[ ! -r "$migration_tmp_dir" -o ! -w "$migration_tmp_dir" ] && echo "Aborting migration: Migration-Tmp-Dir '$migration_tmp_dir' is not readable or writeable." && exit -1

cd $migration_log_dir && rm -rf migration_debug.log && rm -rf migration.log
cd $migration_tmp_dir && rm -rf *

echo "Remove workspace/cache/* and workspace/files/*"
cd $workspace_cache_dir && rm -rf *
cd $workspace_files_dir && rm -rf [0-9]*

echo "Clean database"
cd $db_dir
if [ "$?" -ne "0" ]
then
    echo "Aborting migration: cd '$db_dir'  FAILED."
    exit -1
fi

./createdb.sh
[ $? != 0 ] && echo "Aborting migration: creatdb.sh FAILED." && exit -1

cd $migration_dir

echo "Validation of Opus3-XML-Dumpfile"
php Opus3Migration_Validation.php -f $xml_file
[ $? != 0 ] && echo "Aborting migration: Opus3Migration_Validation.php FAILED." && exit -1

echo "Import institutes, collections and licenses"
php Opus3Migration_ICL.php -f $xml_file
[ $? != 0 ] && echo "Aborting migration: Opus3Migration_ICL.php FAILED." && exit -1

echo "Import metadata and fulltext"
start=1
end=`expr $start + $stepsize - 1`

php Opus3Migration_Documents.php -f $xml_file -p $fulltext_path -s $start -e $end
RETVAL=$?
[ $RETVAL != 0 ] && [ $RETVAL != 1 ] && echo "Aborting migration: Opus3Migration_Documents.php FAILED." && exit -1

while [ "$RETVAL" -eq "1" ] && [ "$iteration" -eq "1" ]
do
    start=`expr $start + $stepsize`
    end=`expr $end + $stepsize`
    if [ "$buildindex" = "1" ]
    then
        cd script_dir
        php SolrIndexBuilder.php
        cd $migration_dir
    fi
    php Opus3Migration_Documents.php -f $xml_file -p $fulltext_path -s $start -e $end
    RETVAL=$?
    [ $RETVAL != 0 ] && [ $RETVAL != 1 ] && echo "Aborting migration: Opus3Migration_Documents.php FAILED." && exit -1
done

cd $script_dir
php SolrIndexBuilder.php
[ $? != 0 ] && echo "Aborting migration: SolrIndexBuilder.php FAILED." && exit -1
cd $migration_dir
