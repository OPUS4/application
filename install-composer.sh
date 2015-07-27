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
# @copyright   Copyright (c) 2010-2011, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id$

set -e

SCRIPT_NAME="$(basename "$0")"
BASEDIR="$1"


# create base folder on demand and qualify its pathname
mkdir -p "$BASEDIR" || exit 1
cd "$BASEDIR"
BASEDIR="$(pwd)"


if [ -e composer.phar ]; then
	# upgrade existing composer
	php composer.phar selfupdate || {
		echo "failed updating composer" >&2
		exit 1
	}
else
	# install composer
	curl -s http://getcomposer.org/installer | php || {
		echo "failed getting composer" >&2
		exit 1
	}
fi


# install all dependencies
php composer.phar install || {
	echo "failed installing dependencies" >&2
	exit 1
}
