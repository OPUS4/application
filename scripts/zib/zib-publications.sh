#!/usr/bin/env bash

##
## Call This Skript with paramaters:
## -p Path for HTML-PublicationLists files (e.g. /home/gunar/pl)
## -h <hours> Print all PLs changed during <hours> Hours
## -d <days> Print alll PLs changed during <days> Days
## -t <type> Which Type of PLs do you need (typo3, standalone)
##

hours=0
days=0
type=file

while getopts p:h:d:t: o
do	case "$o" in
	p)	plpath="$OPTARG";;
        h)	hours="$OPTARG";;
        d)	days="$OPTARG";;
        t)      type="$OPTARG";;
	[?])	print "Usage: $0 [-p plpath] [-h <hours | -d <days> ] [ -t type ]"
		exit 1;;
	esac
done

while [ ! -w "$plpath" ]
do
    echo "Please type a valid and writeable basename for your PublicationLists fulltext files (e.g. /home/user/pl):"
    read pathpath
done

echo $hours | grep "[^0-9]" > /dev/null 2>&1
while [ "$?" -eq "0" ]
do
    echo "Please enter a valid number for hours (e.g. 24)"
    read hours
    echo $hours | grep "[^0-9]" > /dev/null 2>&1
done

echo $days | grep "[^0-9]" > /dev/null 2>&1   
while [ "$?" -eq "0" ]
do
    echo "Please enter a valid number for days (e.g. 7)"                                
    read days   
    echo $days | grep "[^0-9]" > /dev/null 2>&1   
done

if [ $hours -gt 0 ]
then
    echo "php ZIBPublicationLists.php -p $plpath -h $hours -t $type"
    php ZIBPublicationLists.php -p $plpath -h $hours -t $type
elif [ $days -gt 0 ]
then
    echo "php ZIBPublicationLists.php -p $plpath -d $days -t $type"
    php ZIBPublicationLists.php -p $plpath -d $days -t $type
else
    echo "php ZIBPublicationLists.php -p $plpath -t $type"
    php ZIBPublicationLists.php -p $plpath -t $type
fi
