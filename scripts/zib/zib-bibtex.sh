#!/bin/sh

##
## Call This Skript with paramaters:
## -f BibTex-XML file (e.g. /usr/local/opus/complete_database.xml)
## -s Number of first document to import
## -e Number of last document to import
##

while getopts f:p:s:e: o
do	case "$o" in
	f)	bibtexxmlfile="$OPTARG";;
        s)	start="$OPTARG";;
        e)	end="$OPTARG";;
	[?])	print "Usage: $0 [-f bibtexxmlfile] [-s start-id] [-e end-id] "
		exit 1;;
	esac
done

echo "Import metadata from bibtex-file "
echo "php ZIBImport_BibTeX.php -f $bibtexxmlfile -s $start -e $end"
php ZIBImport_BibTeX.php -f $bibtexxmlfile -s $start -e $end

echo "Do you want to build SolrIndex (y/n) ?"
read input

case $input in
"y")
cd ../scripts
php SolrIndexBuilder.php
esac

