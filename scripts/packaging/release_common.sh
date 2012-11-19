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
# @copyright   Copyright (c) 2012, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id$

set -o errexit

# TODO Enable/disable based on script parameters
DEBUG_ENABLED=1
DRYRUN_ENABLED=1

# -----------------------------------------------------------------------------
# Functions
# -----------------------------------------------------------------------------

function DEBUG() {
    [[ "${DEBUG_ENABLED}" -eq 1 ]] && echo 'DEBUG' - "$@"
    return 0
}

function DRYRUN() {
    [ "$DRYRUN_ENABLED" -eq 1 ] && return 0;
    return 1;
}

# Asks yes/no question
# @return 0 = yes = default, 1 = no
function askYesNo() {
    local QUESTION="$1"
    local ANSWER=''

    while [[ -z $ANSWER ]] || [[ $ANSWER != 'y' ]] && [[ $ANSWER != 'n' ]]; do
        echo -e "$QUESTION \c "
        read ANSWER

        if [[ -z $ANSWER ]]; then
            return 0 # default
        else
            ANSWER=${ANSWER,,} # convert to lowercase
            ANSWER=${ANSWER:0:1} # get first letter
        fi

        if [[ $ANSWER != 'y' ]] && [[ $ANSWER != 'n' ]]; then
            echo "Invalid input."
        fi
    done

    if [[ $ANSWER == 'y' ]]; then
        return 0
    else
        return 1
    fi
}