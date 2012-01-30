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
# @copyright   Copyright (c) 2010, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id$

set -o errexit

# TODO Enable/disable based on script parameters
DEBUG_ENABLED=1
DRYRUN=1

# -----------------------------------------------------------------------------
# Functions
# -----------------------------------------------------------------------------

function DEBUG() {
    [[ "${DEBUG_ENABLED}" -eq 1 ]] && echo 'DEBUG' - "$@"
    return 0
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

# TODO add support for REVISION parameter
# TODO enable tagging
function createTag() {
    local COMPONENT="$1"
    local TAG="$2"
    local MESSAGE="$3"
    local SRC="$BASE_URL/$COMPONENT/trunk"
    local DEST="$BASE_URL/$COMPONENT/tags/$TAG"
    echo "Creating tag for '$COMPONENT' ..."
    # svn copy "$SRC" "$DEST" -m "$MESSAGE"
    DEBUG "$SRC"
    DEBUG "$DEST"
    DEBUG "$MESSAGE"
}

# -----------------------------------------------------------------------------
# Main
# -----------------------------------------------------------------------------

# Determine date for tag
DATESTR="$(date +"%Y-%m-%d")"

echo -e "What date should be used for tag [$DATESTR]? \c "
read DATE_ENTERED

if [[ ! -z $DATE_ENTERED ]]; then
    # TODO validate formatting
    DATESTR="$DATE_ENTERED"
fi

echo "Date '$DATESTR' will be used!"

# Determine version for tag
echo -e 'What version should be used for release? \c '
read RELEASE_VERSION

# TODO validate RELEASE_VERSION (empty, format)

TAG="$DATESTR"_"$RELEASE_VERSION"

# Show summary and ask to proceed
echo "Release will be tagged with '$TAG'"

if ! askYesNo 'Continue [Y/n]? \c '; then
    echo 'Aborting tagging!'
    exit 0;
fi

echo 'Creating tags ...'

BASE_URL='https://svn.zib.de/opus4dev'
MESSAGE="Tagging release $RELEASE_VERSION of OPUS 4."

# Create tags for release
createTag 'framework' "$TAG" "$MESSAGE"
createTag 'server' "$TAG" "$MESSAGE"
createTag 'apacheconf' "$TAG" "$MESSAGE"
createTag 'solrconfig' "$TAG" "$MESSAGE"
createTag 'install' "$TAG" "$MESSAGE"
createTag 'documentation' "$TAG" "$MESSAGE"
createTag 'migration' "$TAG" "$MESSAGE"