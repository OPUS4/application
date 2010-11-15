#!/bin/sh

##
## Call This Skript with paramaters:
## -f OPUS3-XML-database export file (e.g. /usr/local/opus/complete_database.xml)
## -p Path your OPUS3 fulltext files (e.g. /usr/local/opus/htdocs/volltexte)
## -s Number of first document to import
## -e Number of last document to import
##

while getopts f:p:s:e: o
do	case "$o" in
	f)	xmlfile="$OPTARG";;
	p)	fulltextpath="$OPTARG";;
        s)	start="$OPTARG";;
        e)	end="$OPTARG";;
	[?])	print "Usage: $0 [-f xmlfile] [-p fulltextpath] [-s start-id] [-e end-id] "
		exit 1;;
	esac
done



echo "Start Opus3-Migration"

# clean files, everything will be imported newly
echo "Do you want to delete all files in workspace-directory (y/n) ?"
read input

case $input in
"y")
cd ../workspace/files/
rm -rf [0-9]*
esac


# create clean database
echo "Do you want to clean database (y/n) ?"
read input

case $input in
"y")
cd ../../db
sh ./createdb.sh
esac

# import institutes, collections and licenses
echo "Do you want to import institutes, collections and licenses (y/n) ?"
read input

case $input in
"y")
cd ../scripts
echo "php Opus3Migration_ICL.php -f $xmlfile"
php Opus3Migration_ICL.php -f $xmlfile
esac

# import metadata and fulltext from documents
echo "Do you want to import metadata and fulltext of documents (y/n) ?"
read input

case $input in
"y")
cd ../scripts
echo "php Opus3Migration_Documents.php -f $xmlfile -p $fulltextpath -s $start -e $end"
php Opus3Migration_Documents.php -f $xmlfile -p $fulltextpath -s $start -e $end
esac

# import metadata and fulltext from documents
echo "Do you want to build SolrIndex (y/n) ?"
read input

case $input in
"y")
cd ../scripts
php SolrIndexBuilder.php
esac

