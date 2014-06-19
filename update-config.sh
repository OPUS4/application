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
# @author      Susanne Gottwald <gottwald@zib.de>
# @author      Jens Schwidder <schwidder@zib.de>
# @copyright   Copyright (c) 2011, OPUS 4 development team
# @license     http://www.gnu.org/licenses/gpl.html General Public License
# @version     $Id$

# Updates the OPUS4 configuration files
#
# Document types are updated after asking the user.

# TODO simply mechanism so that not every file has to be handled separately

set -o errexit

# TODO move into common script? Be careful with main script.

source update-common.sh

setVars

DEBUG "BASEDIR = $BASEDIR"
DEBUG "BASE_SOURCE = $BASE_SOURCE"
DEBUG "MD5_OLD = $MD5_OLD"

DEST="$BASEDIR/opus4/application/configs"
MD5PATH=opus4/application/configs
SRC="$BASE_SOURCE/$MD5PATH"
UPDATE_DOCTYPES_LOG="$BASEDIR/UPDATE-documenttypes.log"
UPDATE_DOCTEMPLATES_LOG="$BASEDIR/UPDATE-documenttemplates.log"

echo "Updating configuration files ..."

DEBUG "Copying $SRC to $DEST"

# The following files are simply copied without checking the existing files.
copyFile "$SRC/application.ini" "$DEST/application.ini"
copyFile "$SRC/config.ini.template" "$DEST/config.ini.template"
# TODO maybe config.ini should be merged with new template? Message to user?

# Copy migration.ini file
copyFile "$SRC/migration.ini" "$DEST/migration.ini"
# Copy migration_config.ini.template file
copyFile "$SRC/migration_config.ini.template" "$DEST/migration_config.ini.template"

# Ask user before replacing the following files if they have been modified.
updateFile "$SRC" "$DEST" "$MD5PATH" "navigation.xml"
updateFile "$SRC" "$DEST" "$MD5PATH" "navigationModules.xml"

# Update document types
# copyFile "$SRC/doctypes/all.xml" "$DEST/doctypes/all.xml" # TODO remove

echo "Updating document types ..."

# =============================================================================
# Updating of XML document type definitions
# =============================================================================

echo "Step 1: Updating XML document type definitions ..."

FILES=$(getFiles "$SRC/doctypes")

for FILE in $FILES; do
    updateFile "$SRC/doctypes" "$DEST/doctypes" "$MD5PATH/doctypes" "$FILE" backup
done

# if updating from version <= 4.1.4 to version >= 4.2.0:
# we need to check all user created doctypes since schema modifications were made
# hint: 4.2.0 > 4.2 is true in lexicographic ordering
if [[ "$VERSION_OLD" < "4.2" && "$VERSION_NEW" > "4.2" ]]; then
    FILES=$(getFiles "$DEST/doctypes")

    for FILE in $FILES; do
        # replace some attribute values if necessary and validate xml document type against new xml schema
        "$SCRIPTPATH/update-documenttypes.php" "$DEST/doctypes/$FILE" "$BASE_SOURCE/opus4/library/Opus/Document/documenttype.xsd" >> "$UPDATE_DOCTYPES_LOG"
    done
fi

# =============================================================================
# Updating & moving of PHTML template files
# when updating from version < 4.4.0 to version >= 4.4.0
# =============================================================================

echo "Step 2: Updating PHTML document type templates ..."

PHTML_FILES_CONFIGS="$BASEDIR/opus4/application/configs/doctypes_templates"

# hint: 4.4.0 > 4.4 is true in lexicographic ordering
if [[ "$VERSION_OLD" < "4.4" && "$VERSION_NEW" > "4.4" ]]; then

    # PHTML doctype templates should be moved from 'modules' to 'configs'
    PHTML_FILES_MODULES="$BASEDIR/opus4/modules/publish/views/scripts/form"

    # Create target folder if necessary
    if [[ ! -d $PHTML_FILES_CONFIGS ]]; then
        createFolder "$PHTML_FILES_CONFIGS"
    fi

    # Copy files if old folder is present (version prior to 4.4.0)
    # check.phtml should remain in PHTML_FILES_MODULES
    if [[ -d $PHTML_FILES_MODULES ]]; then
        echo "Moving PHTML doctype templates from $PHTML_FILES_MODULES to $PHTML_FILES_CONFIGS ..."
        find "$PHTML_FILES_MODULES" -maxdepth 0 -type f \( -name "*.phtml" ! -name "check.phtml" \) -print0 | while read -r -d $'\0' FILE_PATH; do
            FILE=$(basename "$FILE_PATH")
            echo "moving PHTML document view template '$FILE' from '$PHTML_FILES_MODULES' to '$PHTML_FILES_CONFIGS'" >> "$UPDATE_DOCTEMPLATES_LOG"
            moveFile "$PHTML_FILES_MODULES/$FILE" "$PHTML_FILES_CONFIGS/$FILE"
        done
        echo 'done'
    fi

    FILES=$(getFiles "$SRC/doctypes_templates")

    for FILE in $FILES; do
        updateFile "$SRC/doctypes_templates" "$PHTML_FILES_CONFIGS" "opus4/modules/publish/views/scripts/form" "$FILE" backup
    done

else

    FILES=$(getFiles "$SRC/doctypes_templates")

    for FILE in $FILES; do
        updateFile "$SRC/doctypes_templates" "$PHTML_FILES_CONFIGS" "$MD5PATH/doctypes_templates" "$FILE" backup
    done

fi

echo "Update of document types completed."

# =============================================================================
# Updating help files
# =============================================================================

# The help files should be moved from 'modules' to 'configs'.
HELP_FILES_MODULES="$BASEDIR/opus4/modules/home/views/scripts"
HELP_FILES_CONFIGS="$BASEDIR/opus4/application/configs/help"

# Create folder if necessary
if [[ ! -d $HELP_FILES_CONFIGS ]]; then
    createFolder "$HELP_FILES_CONFIGS"
fi

# Copy files if old folder is present (version < 4.2.2)
if [[ -d $HELP_FILES_MODULES ]]; then
    echo "Moving help files from $HELP_FILES_MODULES to $HELP_FILES_CONFIGS ..."
    find "$HELP_FILES_MODULES" -type f -name "*.txt" -print0 | while read -r -d $'\0' FILE_PATH; do
        FILE=$(basename "$FILE_PATH")
        moveFile "$HELP_FILES_MODULES/$FILE" "$HELP_FILES_CONFIGS/$FILE"
    done
    echo 'done'
fi

# TODO OPUSVIER-2354 delete old txt files from "../modules/home/views/scripts"?

# Update help files, but ask user for every modified file
FILES=$(getFiles "$SRC/help")

for FILE in $FILES; do
    updateFile "$SRC/help" "$HELP_FILES_CONFIGS" "$MD5PATH/help" "$FILE" backup
done

# =============================================================================
# Updating Mail Template Files
# =============================================================================

MAIL_TEMPLATES_DIR="$BASEDIR/opus4/application/configs/mail_templates"

if [[ ! -d $MAIL_TEMPLATES_DIR ]]; then
    createFolder "$MAIL_TEMPLATES_DIR"
fi

# Update mail template files, but ask user for every modified file
FILES=$(getFiles "$SRC/mail_templates")

for FILE in $FILES; do
    updateFile "$SRC/mail_templates" "$MAIL_TEMPLATES_DIR" "$MD5PATH/mail_templates" "$FILE" backup
done


# =============================================================================
# Updating solr.xslt file
# =============================================================================

XSLT_DIR="$BASEDIR/opus4/application/configs/solr"
OLD_XSLT_FILE="$BASEDIR/opus4/library/Opus/SolrSearch/Index/solr.xslt"

if [[ ! -d  $XSLT_DIR ]]; then
    createFolder "$XSLT_DIR"
fi

if [[ -f $OLD_XSLT_FILE ]]; then
    moveFile "$OLD_XSLT_FILE" "$XSLT_DIR/solr.xslt"
fi

updateFile "$SRC/solr" "$XSLT_DIR" "