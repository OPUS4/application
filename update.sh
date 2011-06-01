#!/bin/bash
#
# LICENCE
# This code is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This code is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# @author      Susanne Gottwald <gottwald@zib.de>
# @author      Jens Schwidder <schwidder@zib.de>
# @copyright   Copyright (c) 2011, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id$

# Main script for updating an OPUS4 instance
# @param $1 path to OPUS4 installation
# @param $2 path to new distribution
# @param $3 version of OPUS4 installation
#
# Important global variables
# SCRIPTPATH - Path to this script
# BASEDIR - Path to OPUS4 installation
# BASE_SOURCE - Path to new OPUS4 distribution
# VERSION_OLD - Version of OPUS4 installation
# VERSION_NEW - Version of new OPUS4 distribution
# MD5_OLD - Path to MD5 reference file for OPUS4 installation
# MD5_NEW - Path to MD5 reference file for new OPUS4 distribution
# _UPDATELOG - Path to file for update log

# TODO IMPORTANT prevent downgrade
# TODO add backup script
# TODO add generic function for YES/NO questions?
# TODO add function for abort? Including cleanup script?
# TODO add batch mode (no questions asked)?
# TODO Make it possible to revert update if something fails? (Keep old files until done?)
# TODO IMPORTANT The new scripts can delete files. Make sure they won't delete local configuration files (e.g. config.ini, createdb.sh)
# TODO IMPORTANT Should all files instead of being deleted be renamed. If it is in MD5 delete, if not rename?
# TODO refactor for consistent naming of variables
# TODO use flags like RESTART_APACHE, RESTART_SOLR that can be set during the update process to trigger restarts at the end of the process

set -o errexit

# =============================================================================
# Parse parameters
# =============================================================================

# Use first parameter as location of OPUS4 installation
if [[ ! -z $2 ]]; then
    BASEDIR=$1
fi

# Use second parameter as location of OPUS4 distribution
if [[ ! -z $1 ]]; then
    BASE_SOURCE=$2
fi

# Use third parameter as version of old OPUS4
if [[ ! -z $3 ]]; then
    VERSION_OLD=$3
fi

# =============================================================================
# Define constants
# =============================================================================

# Default installation path for OPUS4
BASEDIR_DEFAULT=/var/local/opus4

# =============================================================================
# Define functions
# =============================================================================

# Determines installation directory for existing OPUS4
# TODO handle BASEDIR passed as parameter consistently
function getBasedir() {
    local ABORT='n'
    while [[ -z $BASEDIR ]] || [[ ! -d $BASEDIR ]] && [[ $ABORT != 'y' ]]; do 
        echo -e "Please specify OPUS4 installation directory ($BASEDIR_DEFAULT): \c "
        read BASEDIR_NEW
        if [[ -z "$BASEDIR_NEW" ]]; then
            BASEDIR_NEW=$BASEDIR_DEFAULT
        fi
        # Verify BASEDIR_NEW
        if [[ ! -d $BASEDIR_NEW ]]; then 
            echo "OPUS4 could not be found at $BASEDIR_NEW"
            echo -e "Would you like to abort the update (y/N)? \c ";
            read ABORT
            if [[ -z $ABORT ]]; then 
                ABORT='n'
            else 
                # TODO better way of doing the two steps below
                # TODO removing whitespace (trim) does not seem necessary
                ABORT=${ABORT,,} # convert to lowercase
                ABORT=${ABORT:0:1} # get first letter
            fi
        else
            BASEDIR="$BASEDIR_NEW"
        fi
        unset BASEDIR_NEW
    done
    if [[ $ABORT == 'y' ]]; then 
        echo "OPUS4 update aborted"
        exit 1
    fi
    unset ABORT
}

# Determines current version of installed OPUS4
# TODO What if VERSION.txt is missing, but it is a post 4.1 version?
# TODO What if content of VERSION.txt is wrong (no MD5SUMS file for version)
function getOldVersion() {
    if [[ ! -f $BASEDIR/VERSION.txt ]]; then 
        local ABORT='n'
        while [[ -z $MD5_OLD ]] || [[ ! -f $MD5_OLD ]] && [[ $ABORT != 'y' ]]; do
            # Check if version has been specified as parameter
            if [[ -z $VERSION_OLD ]]; then
                echo -e "What version of OPUS4 is installed? \c "
                read VERSION_OLD
            fi

            getMd5Sums

            # Check if MD5SUMS file exists for the entered version
            # TODO Better way to verify entered version?
            if [[ ! -f $MD5_OLD ]]; then
                echo -e "You entered an unknown OPUS4 version number. Abort the update [y/N]? \c "
                read ABORT
                if [[ -z $ABORT ]]; then 
                    ABORT='n'
                else 
                    # TODO better way of doing the two steps below
                    # TODO removing whitespace (trim) does not seem necessary
                    ABORT=${ABORT,,} # convert to lowercase
                    ABORT=${ABORT:0:1} # get first letter
                fi
                # Unset version to force version question above
                unset VERSION_OLD
            fi
        done
        if [[ $ABORT == 'y' ]]; then 
            echo "OPUS4 update aborted"
            exit 1
        fi
    else 
        # Read content of VERSION into VERSION_OLD
	VERSION_OLD=$(sed -n '1p' "$BASEDIR/VERSION.txt")		
    fi
}

# Determines version of new OPUS4
# TODO Ways to improve, make more robust?
function getNewVersion() {
    VERSION_NEW=$(sed -n '1p' "$BASE_SOURCE/VERSION.txt")
}

# Find MD5SUMS for installed OPUS4
# Use file MD5SUMS if it exists, otherwise 
# TODO Ways to make it more robust?
# TODO rename MD5_OLD to MD5SUMS_INSTALLED or something else
function getMd5Sums() {
    if [[ ! -f $BASEDIR/MD5SUMS ]]; then
        # TODO use SCRIPTPATH?
        MD5_OLD="$BASE_SOURCE/releases/$VERSION_OLD.MD5SUMS"
    else
        MD5_OLD="$BASEDIR/MD5SUMS"
    fi
    DEBUG "MD5_OLD = $MD5_OLD"
}

# TODO move up or down?
source update-common.sh

# Advice user to backup old installation before update
# TODO perform backup to user specified or default location? Ask first.
function backup() {
    echo -e "IMPORTANT: You should backup your OPUS $VERSION_OLD installation\c"
    echo " before running the update. Files will be overwritten!"
    echo -e "Start the update to OPUS $VERSION_NEW now [y/N]? \c "
    read UPDATE_NOW
    if [[ -z $UPDATE_NOW ]]; then 
        UPDATE_NOW='n'
    else
        UPDATE_NOW=${UPDATE_NOW,,}
        UPDATE_NOW=${UPDATE_NOW:0:1}
    fi
    if [[ $UPDATE_NOW != 'y' ]]; then 
        echo "OPUS4 update aborted"
        exit 1
    fi
}

DEBUG "Debug output enabled"
! DRYRUN || echo "Dry-Run mode enabled" # Using ! since the command should not be executed if Dry-Run is enabled

# Get name and path for update script
SCRIPTNAME=`basename $0`
SCRIPTPATH=$(cd `dirname $0` && pwd)

# If BASE_SOURCE was not provided set to parent folder of SCRIPTPATH
if [ -z $BASE_SOURCE ]; then
    BASE_SOURCE=$SCRIPTPATH/..
fi 

DEBUG "BASE_SOURCE = $BASE_SOURCE"
DEBUG "SCRIPTNAME = $SCRIPTNAME"
DEBUG "SCRIPTPATH = $SCRIPTPATH"


MD5_NEW="$BASE_SOURCE/MD5SUMS"

# Switch to folder containing update script
# TODO Is that a problem? Can we do without?
cd $SCRIPTPATH

# Determine BASEDIR for old OPUS4 installation
getBasedir

DEBUG "BASEDIR = $BASEDIR"

# Determine version of old OPUS4 installation
getOldVersion

# Determine version of new OPUS4
getNewVersion 

DEBUG "VERSION_OLD = $VERSION_OLD"
DEBUG "VERSION_NEW = $VERSION_NEW"

# Make sure MD5_OLD is set (in case it was not set by getOldVersion)
if [[ -z $MD5_OLD ]] ; then
    getMd5Sums 
fi

# Create file for update log (sets _UPDATELOG)
INIT_UPDATELOG

backup

# =============================================================================
# Run update scripts
# =============================================================================

export OPUS_UPDATE_BASEDIR=$BASEDIR
export OPUS_UPDATE_BASE_SOURCE=$BASE_SOURCE
export OPUS_UPDATE_MD5_OLD=$MD5_OLD
export OPUS_UPDATE_MD5_NEW=$MD5_NEW
export OPUS_UPDATE_LOG=$_UPDATELOG
export OPUS_UPDATE_VERSION_OLD=$VERSION_OLD
export OPUS_UPDATE_VERSION_NEW=$VERSION_NEW
export OPUS_UPDATE_SCRIPTPATH=$SCRIPTPATH     # TODO necessary? Different way?

# Update configuration
"$SCRIPTPATH"/update-config.sh # "$BASEDIR" "$BASE_SOURCE" "$MD5_OLD" "$_UPDATELOG"

# Update database
"$SCRIPTPATH"/update-db.sh # "$BASEDIR" "$BASE_SOURCE" "$MD5_OLD" "$_UPDATELOG" "$VERSION_OLD" "$VERSION_NEW"

# Update *import* folder
"$SCRIPTPATH"/update-import.sh # "$BASEDIR" "$BASE_SOURCE" "$MD5_OLD" "$_UPDATELOG"

# Update *library* folder
"$SCRIPTPATH"/update-library.sh # "$BASEDIR" "$BASE_SOURCE" "$MD5_OLD" "$_UPDATELOG"

# Update modules
"$SCRIPTPATH"/update-modules.sh # "$BASEDIR" "$BASE_SOURCE" "$MD5_OLD" "$_UPDATELOG" "$MD5_NEW" "$SCRIPTPATH"

# Update *public* folder
"$SCRIPTPATH"/update-public.sh # "$BASEDIR" "$BASE_SOURCE" "$MD5_OLD" "$_UPDATELOG"

# Update *scripts* folders
"$SCRIPTPATH"/update-scripts.sh # "$BASEDIR" "$BASE_SOURCE" "$MD5_OLD" "$_UPDATELOG"

# Update SOLR index
"$SCRIPTPATH"/update-solr.sh # "$BASEDIR" "$BASE_SOURCE" "$MD5_OLD" "$_UPDATELOG" "$VERSION_OLD" 

# Update Apache configuration
"$SCRIPTPATH"/update-apache.sh # "$BASEDIR" "$BASE_SOURCE" "$MD5_OLD" "$_UPDATELOG"

# =============================================================================
# Extra update steps
# =============================================================================

# Update root directory files
updateFolder $BASE_SOURCE $BASEDIR flat
# deleteFiles $BASE_SOURCE $BASEDIR flat # TODO at the moment deletes too much (required folders)

# Create incoming folder if necessary
if [[ ! -d "$BASEDIR/workspace/incoming" ]]; then
    createFolder "$BASEDIR/workspace/incoming"
fi

# Update testdata
updateFolder "$BASE_SOURCE/testdata" "$BASEDIR/testdata"
deleteFiles "$BASE_SOURCE/testdata" "$BASEDIR/testdata"

# Update install
updateFolder "$BASE_SOURCE/install" "$BASEDIR/install"
deleteFiles "$BASE_SOURCE/install" "$BASEDIR/install"

# =============================================================================
# Finish update
# =============================================================================

# TODO Verify successful update somehow?

# Restart apache
if askYesNo "Would you like to restart Apache2 now [Y/n]?"; then
    echo "Restarting Apache server ..."
    DRYRUN || /etc/init.d/apache2 restart
fi

# Restart Solr
if askYesNo "Would you like to restart Solr server (Jetty) now [Y/n]?"; then
    echo "Restarting Jetty server ..."
    DRYRUN || /etc/init.d/opus4-solr-jetty restart
fi

getProperty "$BASEDIR/opus4/application/configs/config.ini" "searchengine.index.port"
SOLR_SERVER_PORT=$PROP_VALUE

DEBUG "SOLR_SERVER_PORT = $SOLR_SERVER_PORT"

# sleep some seconds to ensure the server is running
echo -e "Wait until Solr server is running... \c "
while :; do
    echo -n "."
    wget -q -O /dev/null "http://localhost:$SOLR_SERVER_PORT/solr/admin/ping" && break
    sleep 2
done
echo "done"


# TODO move into separate script for execution after all other update scripts?    
if askYesNo "Would you like to rebuild Solr index now [Y/n]?"; then
    echo "Rebuilding Solr index ..."
    if [[ "$_DRYRUN" -eq 0 ]]; then
        echo -e "Rebuilding Solr index ... \c "
        php5 "$BASEDIR/opus4/scripts/SolrIndexBuilder.php"
        echo "done"
    fi
fi