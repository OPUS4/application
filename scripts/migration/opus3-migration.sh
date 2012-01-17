#!/usr/bin/env bash

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

script_migration_dir=$(cd `dirname $0` && pwd)
# path to directory thats hosts migration log files
scripts_migration_log_dir=$script_migration_dir/log
# path to directory that hosts temporary migration files
scripts_migration_tmp_dir=$script_migration_dir/tmp
# path to script directory
script_dir=$script_migration_dir/..
# path to workspace directory
workspace_dir=$script_dir/../workspace
# path to workspace directory that hosts fulltexts
workspace_files_dir=$workspace_dir/files
# path to workspace directory that hosts cached files
workspace_cache_dir=$workspace_dir/cache
# path to db directory
db_dir=$script_dir/../db

while [ ! -f "$xmlfile" ]
do
    echo "Please type the name of a dumpfile of the database in XML format (e.g. /usr/local/opus/complete_database.xml):"
    read xmlfile
done

xml_file=$(readlink -f $xmlfile)

while [ ! -d "$fulltextpath" ]
do
    echo "Please type the path to your OPUS3 fulltext files (e.g. /usr/local/opus/htdocs/volltexte):"
    read fulltextpath
done

fulltext_path=$(readlink -f $fulltextpath)

echo $stepsize | grep "[^0-9]" > /dev/null 2>&1
while [ "$?" -eq "0" ]
do
    echo "Please enter a valid number of documents to be imported per each loop (e.g. 50)"
    read stepsize
    echo $stepsize | grep "[^0-9]" > /dev/null 2>&1
done

echo "Clean workspace/files/* and migration/log/* and migration/tmp/* and workspace/cache/* "
cd $workspace_files_dir
if [ "$?" -eq "0" ]
then
    rm -rf [0-9]*
fi

cd $scripts_migration_log_dir
if [ "$?" -eq "0" ]
then
    rm -rf migration_debug.log
    rm -rf migration_error.log
fi

cd $scripts_migration_tmp_dir
if [ "$?" -eq "0" ]
then
    rm -rf *
fi

cd $workspace_cache_dir
if [ "$?" -eq "0" ]
then
    rm -rf *
fi

echo "Clean database"
cd $db_dir
./createdb.sh

echo "Import institutes, collections and licenses"
cd $script_migration_dir
php Opus3Migration_ICL.php -f $xml_file

echo "Import metadata and fulltext"
start=1
end=`expr $start + $stepsize - 1`

php Opus3Migration_Documents.php -f $xml_file -p $fulltext_path -s $start -e $end
while [ "$?" -eq "1" ] && [ "$iteration" -eq "1" ]
do
    start=`expr $start + $stepsize`
    end=`expr $end + $stepsize`
    if [ "$buildindex" = "1" ]
    then
        cd script_dir
        php SolrIndexBuilder.php
        cd $script_migration_dir
    fi
    php Opus3Migration_Documents.php -f $xml_file -p $fulltext_path -s $start -e $end
 done

cd $script_dir
php SolrIndexBuilder.php
cd $script_migration_dir
