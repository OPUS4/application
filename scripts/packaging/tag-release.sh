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

source release_common.sh

# TODO add support for REVISION parameter
# TODO enable tagging
function createTag() {
    local COMPONENT="$1"
    local TAG="$2"
    local MESSAGE="$3"
    local SRC="$BASE_URL/$COMPONENT/trunk"
    local DEST="$BASE_URL/$COMPONENT/tags/$TAG"
    echo "Creating tag for '$COMPONENT' ..."
    DRYRUN || svn copy "$SRC" "$DEST" -m "\"$MESSAGE\""
    DEBUG "$SRC"
    DEBUG "$DEST"
    DEBUG "$MESSAGE"
}

# -----------------------------------------------------------------------------
# Main
# -----------------------------------------------------------------------------

DATE_ENTERED=$1
RELEASE_VERSION=$2

if [[ -z $DATE_ENTERED ]]; then
    # Determine date for tag
    DATESTR="$(date +"%Y-%m-%d")"

    echo -e "What date should be used for tag [$DATESTR]? \c "
    read DATE_ENTERED

    if [[ ! -z $DATE_ENTERED ]]; then
        # TODO validate formatting
        DATESTR="$DATE_ENTERED"
    fi
else
    DATESTR="$DATE_ENTERED"
fi

echo "Date '$DATESTR' will be used!"

if [[ -z $RELEASE_VERSION ]]; then
    # Determine version for tag
    echo -e 'What version should be used for release? \c '
    read RELEASE_VERSION

fi

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