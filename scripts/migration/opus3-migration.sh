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

if [ ! -f "$migration_ini" ]
then
    echo "Configurationfile '`readlink -f $migration_ini`' does not exist or is not readable."
    exit -1
fi

if [ ! -f "$migration_config_ini" ]
then
    echo "Configurationfile '`readlink -f $migration_config_ini`' does not exist or is not readable."
    exit -1
fi

if [ ! -f "$xmlfile" ]
then
    echo "Opus3-XML-Dumpfile '$xmlfile' does not exist or is not readable."
    exit -1
fi

xml_file=$(readlink -f $xmlfile)
xmllint --noout "$xml_file"
if [ "$?" -ne "0" ]; then
    echo "given Opus3-XML-Dumpfile is not well-formed"
    exit
fi

if [ ! -d "$fulltextpath" ]
then
    echo "Opus3-Fulltextpath '$fulltextpath' does not exist or is not readable."
    exit -1
fi

fulltext_path=$(readlink -f $fulltextpath)

[ -z "${stepsize##*[!0-9]*}" ] && echo "Aborting migration: '$stepsize' is not a valid number." && exit -1

echo "Remove migration/log/* and migration/tmp/*"
if [ ! -d "$migration_log_dir" ]
then
    mkdir $migration_log_dir
fi

cd $migration_log_dir
if [ "$?" -eq "0" ]
then
    rm -rf migration_debug.log
    rm -rf migration.log
fi

if [ ! -d "$migration_tmp_dir" ]
then
    mkdir $migration_tmp_dir
fi

cd $migration_tmp_dir
if [ "$?" -eq "0" ]
then
    rm -rf *
fi

echo "Remove workspace/cache/* and workspace/files/*"
cd $workspace_cache_dir
if [ "$?" -eq "0" ]
then
    rm -rf *
fi

cd $workspace_files_dir
if [ "$?" -eq "0" ]
then
    rm -rf [0-9]*
fi

echo "Clean database"
cd $db_dir
if [ "$?" -ne "0" ]
then
    echo "Aborting migration: cd '$db_dir'  FAILED."
    exit -1
fi

./createdb.sh
[ $? != 0 ] && echo "Aborting migration: creatdb.sh FAILED." && exit -1

echo "Import institutes, collections and licenses"
cd $migration_dir
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
