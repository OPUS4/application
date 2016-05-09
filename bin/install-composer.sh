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
# @author      Thomas Urban <thomas.urban@cepharum.de>
# @author      Jens Schwidder <schwidder@zib.de>
# @copyright   Copyright (c) 2010-2016, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License

#
# Skript for installing composer and OPUS 4 dependencies.
#
# Call like this "bin/install-composer.sh ." from the base directory of the
# OPUS 4 application to install composer.phar into the same directory and
# install the necessary dependencies.
#
# Parameters:
#   1) Directory for installing composer
#

set -e

SCRIPT_NAME="$(basename "$0")"
SCRIPT_NAME_FULL="`readlink -f "$0"`"
SCRIPT_PATH="`dirname "$SCRIPT_NAME_FULL"`"


BASEDIR="`dirname "$SCRIPT_PATH"`"

# get BASEDIR from first argument if present
if [ $# -ge 1 ] ;
then
    BASEDIR="$1"
fi

# Don't run Composer as root - Composer itself warns against that
if [[ $EUID -eq 0 ]]; then
    echo -e "\nERROR: This script must not be run as root.\n" 1>&2
    exit 1
fi

# create base folder on demand and qualify its pathname
mkdir -p "$BASEDIR" || exit 1
cd "$BASEDIR"
BASEDIR="$(pwd)"

if [ -e composer.phar ] ;
then
	# upgrade existing composer
	php composer.phar selfupdate || {
		echo "failed self-updating composer" >&2
		exit 1
	}

	php composer.phar update || {
		echo "failed updating dependencies" >&2
		exit 1
	}
else
	# install composer
	curl -s http://getcomposer.org/installer | php || {
		echo "failed getting composer" >&2
		exit 1
	}

	# install all dependencies
	php composer.phar install || {
		echo "failed installing dependencies" >&2
		exit 1
	}
fi
