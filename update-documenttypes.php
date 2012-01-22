#!/usr/bin/env php5
<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class DocumentTypeDefinitionUpdater {

    private $filename;

    private $schema;

    private $xml;

    public function __construct($filename, $schema) {
        $this->filename = $filename;
        $this->schema = $schema;
        $this->xml = new DomDocument();
        $this->xml->load($filename);
    }

    public function run() {
        $this->performUpdate();
        if (!is_null($this->schema)) {
            $this->invalidateFileIfNecessary($this->schema);
        }
    }

    private function printValidationErrors() {
	foreach (libxml_get_errors() as $error) {
	   echo '[line: ' . $error->line . ' column: ' . $error->column . '] ' . trim($error->message) . "\n"; 	
	}
	libxml_clear_errors();
    }

    private function invalidateFileIfNecessary($schema) {
	libxml_use_internal_errors(true);
	libxml_clear_errors();
        if (!$this->xml->schemaValidate($schema)) {
            $date = new DateTime();
            $newname = $this->filename . '.invalid.' . $date->getTimestamp();
            echo "validation error: " . $this->filename . " is not schema-valid\n";
            $this->printValidationErrors();
            echo 'trying to rename ' . $this->filename . " to $newname : ";
            if (file_exists($newname)) {
                echo 'could not rename ' . $this->filename . " to $newname -- file already exist\n";
                return;
            }
            if (!rename($this->filename, $newname)) {
                echo 'an error occurred while trying to rename ' . $this->filename . " to $newname\n";
                return;
            }
            echo "OK\n";
        }
    }

    private function createBackupFile() {
        $date = new DateTime();
        $backupFilename = $this->filename . '.update-backup.' . $date->getTimestamp();
        echo "trying to create backup file $backupFilename : ";
        if (file_exists($backupFilename)) {
            echo "could not create backup file $backupFilename -- file already exist\n";
            return;
        }
        if (!copy($this->filename, $backupFilename)) {
            echo "an error occurred while trying to create backup file $backupFilename\n";
            return;
        }
        echo "OK\n";
    }

    private function performUpdate() {
	$updatesMade = false;
        $updatesMade = $this->updateIdentifiers() ? true : $updatesMade;
        $updatesMade = $this->updateNote() ? true : $updatesMade;
        $updatesMade = $this->updateSubjects() ? true : $updatesMade;
        $updatesMade = $this->updateSeries() ? true : $updatesMade;
	if ($updatesMade) {
            echo $this->filename . " needs to be updated: creating beackup file\n";
            $this->createBackupFile();
            $this->xml->save($this->filename);
	}       
    }

    private function updateIdentifiers() {
        $listOfIdentifiers = array(
            'IdentifierOld',
            'IdentifierSerial',
            'IdentifierUuid',
            'IdentifierDoi',
            'IdentifierHandle',
            'IdentifierOpus3',
            'IdentifierIsbn',
            'IdentifierIssn',
            'IdentifierUrn',
            'IdentifierOpac',
            'IdentifierUrl',
            'IdentifierStdDoi',
            'IdentifierCrisLink',
            'IdentifierSplashUrl',
            'IdentifierPubmed',
            'IdentifierArxiv'
        );
        $restrictions = array (
            'name' => $listOfIdentifiers,
            'datatype' => array('Text', 'text')
        );
        return $this->updateAttributeValue($restrictions, 'Identifier');
    }

    private function updateNote() {
        $restrictions = array (
            'name' => array('Note'),
            'datatype' => array('Text', 'text')
        );
        return $this->updateAttributeValue($restrictions, 'Note');
    }

    private function updateSubjects() {
        $restrictions = array (
            'name' => array('SubjectSwd', 'SubjectUncontrolled'),
            'datatype' => array('Text', 'text')
        );
        return $this->updateAttributeValue($restrictions, 'Subject');
    }

    private function updateSeries() {
        $restrictions = array (
            'name' => array('Series'),
            'datatype' => array('Collection'),
            'root' => array('series')
        );
        return $this->updateAttributeValue($restrictions, 'Series', array('root'));
    }

    private function updateAttributeValue($restrictions, $replacementForDatatypeAttribute, $attributesToRemove = array()) {
        $nodes = $this->xml->getElementsByTagName('field');
        foreach ($nodes as $node) {
            $allChecksPassed = true;
            foreach ($restrictions as $name => $values) {
                $attributeValue = $node->getAttribute($name);
                if (!in_array($attributeValue, $values)) {
                    $allChecksPassed = false;
                    break;
                }
            }
            if ($allChecksPassed) {
                $node->setAttribute('datatype', $replacementForDatatypeAttribute);
                foreach ($attributesToRemove as $attribute) {
                    $node->removeAttribute($attribute);
                }
                return true;
            }
        }
        return false;
    }

}
if ($argc < 2) {
    echo "missing argument filename\n";
    exit;
}
if (!is_readable($argv[1])) {
    echo 'given file ' . $argv[1] . " is not readable\n";
    exit;
}
if (pathinfo($argv[1], PATHINFO_EXTENSION) !== 'xml') {
    // only xml files are processed
    exit;
} 
$schema = null;
if ($argc > 2) {
    // xml schema file for validation is specified
    $schema = $argv[2];
}
$updater = new DocumentTypeDefinitionUpdater($argv[1], $schema);
$updater->run();
