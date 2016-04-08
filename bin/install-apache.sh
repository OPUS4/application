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

set -e

SCRIPT_NAME="`basename "$0"`"
SCRIPT_NAME_FULL="`readlink -f "$0"`"
SCRIPT_PATH="`dirname "$SCRIPT_NAME_FULL"`"
BASEDIR="$(dirname "$SCRIPT_PATH")"

# Get command line parameters
OPUS_URL_BASE="$1"
INPUT_FILENAME="$2"
OUTPUT_FILENAME="$3"
OS="$4"

# Set defaults if necessary
[[ -z $OPUS_URL_BASE ]] && OPUS_URL_BASE='/opus4'
[[ -z $INPUT_FILENAME ]] && INPUT_FILENAME='apache24.conf.template'
[[ -z $OUTPUT_FILENAME ]] && OUTPUT_FILENAME='apache.conf'
[[ -z $OS ]] && OS='ubuntu'

OUTPUT_FILE="$BASEDIR/apacheconf/$OUTPUT_FILENAME"
INPUT_FILE="$BASEDIR/apacheconf/$INPUT_FILENAME"

# TODO warn if existing output file?

# Prepare Apache2 configuration file
# - replace /OPUS_URL_BASE with base URL for OPUS 4 Instanz
# - replace /BASEDIR with path to OPUS 4 base directory
sed -e "s!/OPUS_URL_BASE!/$OPUS_URL_BASE!g; s!/BASEDIR/!/$BASEDIR/!; s!//*!/!g" "$INPUT_FILE" > "$OUTPUT_FILE"

# Enables setting to prevent PHP deleting cookies in Ubuntu
if [ "$OS" = ubuntu ]
then
  sed -i -e 's!#Enable for UBUNTU/DEBIAN:# !!' "$OUTPUT_FILE"
fi


