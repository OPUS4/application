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

# Script prepares and installs Apache 2 configuration file for OPUS 4.

# Parameters:
# - base url (default: /opus4)
# - input file name (default: apache.conf.template)
# - output file name (default: apache.conf)
# - os: (default: ubuntu)
#
# The base url is also used as site name, e.g. '/opus4' -> opus4.conf, when
# creating the link into the sites-available folder of Apache2 on Ubuntu.
#
# TODO protection against using an existing site?
# TODO handle updating/creating configuration of site already available in Apache2
#

set -e

# TODO determine dynamically or make configurable?
APACHE_SITE_DIR='/etc/apache2/sites-available'


SCRIPT_NAME="`basename "$0"`"
SCRIPT_NAME_FULL="`readlink -f "$0"`"
SCRIPT_PATH="`dirname "$SCRIPT_NAME_FULL"`"
BASEDIR="$(dirname "$SCRIPT_PATH")"

# Get command line parameters
OPUS_URL_BASE="$1"
INPUT_FILENAME="$2"
OUTPUT_FILENAME="$3"
OS="$4"
RESTART_APACHE="$5"

# Apache Version
if [ -z "$INPUT_FILENAME" ] ;
then
    # if version 2.2 use apache22 template otherwise use default defined below
    [[ $(apache2 -v | grep "Apache/2.2") ]] && INPUT_FILENAME='apache22.conf.template'
fi

# Set defaults if necessary
[[ -z $OPUS_URL_BASE ]] && OPUS_URL_BASE='/opus4'
[[ -z $INPUT_FILENAME ]] && INPUT_FILENAME='apache24.conf.template'
[[ -z $OUTPUT_FILENAME ]] && OUTPUT_FILENAME='apache.conf'
[[ -z $OS ]] && OS='ubuntu'

OUTPUT_FILE="$BASEDIR/apacheconf/$OUTPUT_FILENAME"
INPUT_FILE="$BASEDIR/apacheconf/$INPUT_FILENAME"

# Check if output file exists
if [ -e "$OUTPUT_FILE" ] ;
then
  read -p "File $OUTPUT_FILE already exists. Create backup? [Y]: " REPLACE_FILE

  if [ -z "$REPLACE_FILE" ] || [ "$REPLACE_FILE" = Y ] || [ "$REPLACE_FILE" = Y ] ;
  then
    cp --backup=numbered "$OUTPUT_FILE" "$OUTPUT_FILE.bak"
  fi

fi

#
# Prepare Apache2 configuration file
# - replace /OPUS_URL_BASE with base URL for OPUS 4 Instanz
# - replace /BASEDIR with path to OPUS 4 base directory
#
sed -e "s!/OPUS_URL_BASE!/$OPUS_URL_BASE!g; s!/BASEDIR/!/$BASEDIR/!; s!//*!/!g" "$INPUT_FILE" > "$OUTPUT_FILE"

# Enable setting to prevent PHP deleting cookies in Ubuntu
if [ "$OS" = ubuntu ]
then
  sed -i -e 's!#Enable for UBUNTU/DEBIAN:# !!' "$OUTPUT_FILE"
fi

[ -z "$APACHE_ADD_SITE" ] && read -p "Add site to Apache2 [Y]: " APACHE_ADD_SITE

if [ -z "$APACHE_ADD_SITE" ] || [ "$APACHE_ADD_SITE" = Y ] || [ "$APACHE_ADD_SITE" = y ] ;
then
    #
    # Enable site in Apache2
    # -requires 'sudo'
    #
    # APACHE_SITE_NAME = OPUS_BASE_URL (ohne '/')
    #

    APACHE_SITE_NAME="$OPUS_URL_BASE"
    APACHE_SITE_NAME="${APACHE_SITE_NAME#"/"}" # removes leading '/'

    # Create link to configuration file
    ln -s "$OUTPUT_FILE" "$APACHE_SITE_DIR/$APACHE_SITE_NAME.conf"

    a2ensite "$APACHE_SITE_NAME"
    apache2ctl configtest

    #
    # Restart Apache2 (optionally)
    # - requires 'sudo'
    #

    [[ -z "$RESTART_APACHE" ]] && read -p "Restart Apache2 [Y]? " RESTART_APACHE

    if [[ -z "$RESTART_APACHE" || "$RESTART_APACHE" = Y || "$RESTART_APACHE" = y ]] ;
    then
      # TODO Ubuntu specific - support other systems?
      service apache2 restart
    fi
fi
