#!/bin/bash

# This file is part of OPUS. The software OPUS has been originally developed
# at the University of Stuttgart with funding from the German Research Net,
# the Federal Department of Higher Education and Research and the Ministry
# of Science, Research and the Arts of the State of Baden-Wuerttemberg.
#
# OPUS 4 is a complete rewrite of the original OPUS software and was developed
# by the Stuttgart University Library, the Library Service Center
# Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
# the Saarland University and State Library, the Saxon State Library -
# Dresden State and University Library, the Bielefeld University Library and
# the University Library of Hamburg University of Technology with funding from
# the German Research Foundation and the European Regional Development Fund.
#
# LICENCE
# OPUS is free software; you can redistribute it and/or modify it under the
# terms of the GNU General Public License as published by the Free Software
# Foundation; either version 2 of the Licence, or any later version.
# OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
# WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
# details. You should have received a copy of the GNU General Public License
# along with OPUS; if not, write to the Free Software Foundation, Inc., 51
# Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.

set -e

script_dir=$(cd `dirname $0` && pwd)

VERBOSE=0

while getopts "v" opt; do
  case $opt in
    v) VERBOSE=1
    ;;
  esac
done

#
# Application Workspace Directories
#

# path to workspace directory
workspace_dir=$script_dir/../workspace
# path to workspace directory that hosts fulltexts
workspace_files_dir=$workspace_dir/files
# path to workspace directory thats hosts log files
workspace_log_dir=$workspace_dir/log
# path to workspace directory that hosts temporary files
workspace_tmp_dir=$workspace_dir/tmp
# path to workspace directory that hosts cached files
workspace_cache_dir=$workspace_dir/cache

# path to directory of series_logos
series_logos_dir=$script_dir/../public/series_logos

#
# Files and Directiories associated with test documents / data
#

# path to directory that contains fulltexts of test documents
fulltext_dir=$script_dir/fulltexts
# path to test workspace
workspace_test_dir=$script_dir/workspace
# path to directory of test series logos
test_series_logos_dir=$script_dir/series_logos

#
# end editable part
#

#
# Rebuild database
#
php rebuild-database.php

#
# Backup old fulltexts and log files and series logos
#

TEMP_DIR=$(mktemp -d $workspace_tmp_dir/old-XXXXXXX)
mkdir -v "$TEMP_DIR"/{files,log}

if [ -d ${workspace_files_dir} ] ; then
    mv $workspace_files_dir/ $TEMP_DIR/files
fi

if [ -d ${workspace_log_dir} ] ; then
    mv $workspace_log_dir/ $TEMP_DIR/log
fi

if [ -d ${series_logos_dir} ] ; then
    mv $series_logos_dir $TEMP_DIR
fi

mkdir -p $workspace_files_dir
mkdir -p $workspace_log_dir
mkdir -p $workspace_dir/cache
mkdir -p $workspace_dir/export
mkdir -p $workspace_dir/incoming
mkdir -p $workspace_dir/tmp
mkdir -p $workspace_dir/tmp/resumption

echo -e "\n*** Created backup of fulltexts and log files in $TEMP_DIR ***\n"

mkdir -p $series_logos_dir

echo -e "\n*** Created backup of fulltexts, log files and series logos in $TEMP_DIR ***\n"

rm -rf $workspace_test_dir/*

mkdir -p $workspace_test_dir/cache
mkdir -p $workspace_test_dir/export
mkdir -p $workspace_test_dir/incoming
mkdir -p $workspace_test_dir/tmp
mkdir -p $workspace_test_dir/tmp/resumption
mkdir -p $workspace_test_dir/files
mkdir -p $workspace_test_dir/log

#
# Copy test fulltexts to workspace
#

echo -e "\n Copying default fulltext files ...\n"

if [[ $VERBOSE -eq 1 ]] ; then
    rsync -rv $fulltext_dir/ $workspace_files_dir
    rsync -rv $fulltext_dir/ $workspace_test_dir/files
else
    rsync -r $fulltext_dir/ $workspace_files_dir
    rsync -r $fulltext_dir/ $workspace_test_dir/files
fi


#
# Restore log files
#
if [ ! -d ${workspace_log_dir} ] ; then
   mkdir -p ${workspace_log_dir}
fi
touch "$workspace_log_dir"/{opus.log,opus-console.log}
chmod -R o+w,g+w {$workspace_files_dir,$workspace_log_dir}

#
# Copy test series logos to public/series_logos
#

echo -e "\n Copying default series logo files ...\n"

if [[ $VERBOSE -eq 1 ]] ; then
    rsync -rv --exclude=.svn $test_series_logos_dir/ $series_logos_dir
else
    rsync -r --exclude=.svn $test_series_logos_dir/ $series_logos_dir
fi