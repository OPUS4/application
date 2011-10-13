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

while [ ! -f "$xmlfile" ]
do
    echo "Please type the name of a dumpfile of the database in XML format (e.g. /usr/local/opus/complete_database.xml):"
    read xmlfile
done

while [ ! -d "$fulltextpath" ]
do
    echo "Please type the path to your OPUS3 fulltext files (e.g. /usr/local/opus/htdocs/volltexte):"
    read fulltextpath
done

echo $stepsize | grep "[^0-9]" > /dev/null 2>&1
while [ "$?" -eq "0" ]
do
    echo "Please enter a valid number of documents to be imported per each loop (e.g. 50)"
    read stepsize
    echo $stepsize | grep "[^0-9]" > /dev/null 2>&1
done

echo "Clean workspace/files/* and workspace/log/import.log and workspace/tmp/* and workspace/cache/* "
cd ../../workspace/files/
if [ "$?" -eq "0" ]
then
    rm -rf [0-9]*
fi
cd ../log/
if [ "$?" -eq "0" ]
then
    rm -rf import_debug.log
    rm -rf import_error.log
fi
cd ../tmp/
if [ "$?" -eq "0" ]
then
    rm -rf *
fi
cd ../cache/
if [ "$?" -eq "0" ]
then
    rm -rf *
fi

echo "Clean database"
cd ../../db
./createdb.sh

echo "Import institutes, collections and licenses"
cd ../scripts/migration
php Opus3Migration_ICL.php -f $xmlfile

echo "Import metadata and fulltext"
start=1
end=`expr $start + $stepsize - 1`

php Opus3Migration_Documents.php -f $xmlfile -p $fulltextpath -s $start -e $end
while [ "$?" -eq "1" ] && [ "$iteration" -eq "1" ]
do
    start=`expr $start + $stepsize`
    end=`expr $end + $stepsize`
    if [ "$buildindex" = "1" ]
    then
        cd ..
        php SolrIndexBuilder.php
        cd ./migration
    fi
    php Opus3Migration_Documents.php -f $xmlfile -p $fulltextpath -s $start -e $end
 done

cd ..
php SolrIndexBuilder.php
cd ./migration
