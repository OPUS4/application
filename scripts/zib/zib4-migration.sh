#!/usr/bin/env bash

##
## Call This Skript with paramaters:
## -f OPUS4-XML-database export file (e.g. /usr/local/opus/complete_database.xml)
## -p Path your OPUS4 fulltext files (e.g. /usr/local/opus/htdocs/volltexte)
## -z Stepsize for looping
## -i Build Index after each loop
##

stepsize=50

while getopts f:p:z:i o
do	case "$o" in
	f)	xmlfile="$OPTARG";;
	p)	fulltextpath="$OPTARG";;
        z)	stepsize="$OPTARG";;
        i)      buildindex=1;;
	[?])	print "Usage: $0 [-f xmlfile] [-p fulltextpath] [-z stepsize for looping] [-i ] "
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
    echo "Please type the path to your OPUS4 fulltext files (e.g. /usr/local/opus/htdocs/volltexte):"
    read fulltextpath
done

echo $stepsize | grep "[^0-9]" > /dev/null 2>&1
while [ "$?" -eq "0" ]
do
    echo "Please enter a valid number of documents to be imported per each loop (e.g. 50)"
    read stepsize
    echo $stepsize | grep "[^0-9]" > /dev/null 2>&1
done

echo "Import metadata and fulltext"
cd ../scripts
start=1
end=`expr $start + $stepsize - 1`

php Opus4Migration_Documents.php -f $xmlfile -p $fulltextpath -s $start -e $end
