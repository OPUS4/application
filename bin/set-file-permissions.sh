#!/usr/bin/env bash
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
# @author      Jens Schwidder <schwidder@zib.de>
# @copyright   Copyright (c) 2010-2016, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License

#
# Sets permissions for workspace directories.
#
# Parameters:
# -g : Group name
# -u : User name
#
# TODO what user should be used?
# TODO query user for setting permissions on other folders (language_custom, config files)?
# TODO add special handling for access to config.ini (more restricted?)
# TODO add special handling for access to resources that can be edited in setup pages
#

set -e

if [[ $EUID -ne 0 ]]; then
    echo -e "\nERROR: This script must be run as root\n" 1>&2
    exit 1
fi

# Get command line parameters
while getopts ":g:u:" opt; do
  case $opt in
    g) OPUS_GROUP_NAME="$OPTARG"
    ;;
    u) OPUS_USER_NAME="$OPTARG"
    ;;
  esac
done

SCRIPT_NAME="`basename "$0"`"
SCRIPT_NAME_FULL="`readlink -f "$0"`"
SCRIPT_PATH="`dirname "$SCRIPT_NAME_FULL"`"

BASEDIR="`dirname "$SCRIPT_PATH"`"

#
# Determine owner and group if not provided as command line options
#

[ -z "$OPUS_USER_NAME" ] && OPUS_USER_NAME="$(stat -c %U "$BASEDIR")"

# TODO [ -z "$OPUS_GROUP_NAME" ] && OPUS_GROUP_NAME="$(stat -c %G "$BASEDIR")"
# TODO needed or better? OPUS_GROUP_NAME="`id -gn "$OPUS_USER_NAME"`"

# Use Apache2 group (Ubuntu) so web server has access
OPUS_GROUP_NAME="${OPUS_GROUP_NAME:-www-data}"

#
# Preparing OWNER string for chown-calls.
#

OWNER="$OPUS_USER_NAME:$OPUS_GROUP_NAME"

#
# Change file owner of all files in $BASEDIR to $OPUS_USER_NAME
#

echo "Setting owner to $OWNER ..."
chown -R "$OWNER" "$BASEDIR"

#
# Set permission in workspace directory appropriately
# TODO not sure if readlink is necessary
echo "Setting workspace permissions ..."

cd "$(readlink "$BASEDIR/workspace")"

# Setting full access for owner and group but not others
find workspace -type d -print0 | xargs -0 -- chmod 770
find workspace -type f -print0 | xargs -0 -- chmod 660

