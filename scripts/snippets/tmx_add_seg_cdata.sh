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
# @author      Edouard Simon <edouard.simon@zib.de>
# @copyright   Copyright (c) 2014, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id$

# This script encloses text within <seg> elements with a CDATA element
# in all .tmx files where no CDATA elements are found.

# CAUTION: 
# 1. Files are ignored where a CDATA element is present in some but not all of the <seg> tags.
# 2. ALL ".tmx" files within the current directory or any of it's subdirectories are parsed and possibly altered.

# TODO move this is a development script


find . -name *.tmx -exec grep -L '<seg><!\[CDATA\[' {} \; | xargs sed -i -e 's#<seg>#<seg><![CDATA[#g' -e 's#</seg>#]]></seg>#g'