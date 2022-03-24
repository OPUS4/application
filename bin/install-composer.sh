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
# @copyright   Copyright (c) 2010-2022, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License

set -e

SCRIPT_NAME_FULL="`readlink -f "$0"`"
SCRIPT_PATH="`dirname "$SCRIPT_NAME_FULL"`"

BASEDIR="`dirname "$SCRIPT_PATH"`"

# Don't run Composer as root - Composer itself warns against that
if [[ $EUID -eq 0 ]]; then
    echo -e "\nERROR: This script must not be run as root.\n" 1>&2
    exit 1
fi

# get BASEDIR from first argument if present
if [ $# -ge 1 ] ;
then
    INSTALL_PACKAGES=1
fi

if [ -e bin/composer ] ;
then
  if [[ "$INSTALL_PACKAGES" == 1 ]] ;
  then
    cd $BASEDIR
    php bin/composer install
  fi
  exit 1
fi

EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]
then
    >&2 echo 'ERROR: Invalid installer checksum'
    rm composer-setup.php
    exit 1
fi

php composer-setup.php --quiet --install-dir="$BASEDIR/bin" --filename=composer
RESULT=$?
rm composer-setup.php

if [[ "$INSTALL_PACKAGES" == 1 ]] ;
then
  cd $BASEDIR
  php bin/composer install
fi

exit $RESULT
