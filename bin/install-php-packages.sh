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
# @author      Jens Schwidder <schwidder@zib.de>
# @copyright   Copyright (c) 2010-2020, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
#

#
# The purpose of this script is to make installation of all necessary PHP
# packages for OPUS 4 easy. The script ist written for Ubuntu 16 and might
# also work on other platforms using 'apt-get' to install packages.
#

if [[ $EUID -ne 0 ]]; then
    echo -e "\nERROR: This script must be run as root\n" 1>&2
    exit 1
fi

if hash apt-get 2>/dev/null; then
    # Installs required PHP packages on Ubuntu/Debian
    apt-get install php
    apt-get install php-cli
    apt-get install php-common
    apt-get install php-curl
    apt-get install php-dev
    apt-get install php-gd
    apt-get install php-mcrypt
    apt-get install php-mysql
    apt-get install php-xsl
    apt-get install php-log
    apt-get install php-zip
    apt-get install php-intl
    apt-get install php-mbstring
    apt-get install libapache2-mod-php
else
    echo -e "\nERROR: This script requires 'apt-get' to install packages.\n" 1>&2
    exit 1
fi

