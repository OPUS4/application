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
 * @category    Application
 * @author      Gunar Maiwald <szott@zib.de>
 * @copyright   Copyright (c) 2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: export_all_docs.php 9044 2011-10-13 16:11:16Z gmaiwald $
 */


/**
 * Tries to export all documents.
 */

require_once dirname(__FILE__) . '/common/bootstrap.php';

$config = Zend_Registry::get('Zend_Config');
$exportFile = $config->workspacePath . DIRECTORY_SEPARATOR . "export" . DIRECTORY_SEPARATOR . "export.xml";

// Exception if not writeable
try {
    if(!is_writeable($exportFile)) {
        throw new Exception('exportfile is not writeable');
    }
} catch (Exception $e) {
    echo $e;
    exit();
}


$docFinder = new Opus_DocumentFinder();
$opusDocuments = new DOMDocument('1.0', 'utf-8');
$opusDocuments->formatOutput = true; 
$export = $opusDocuments->createElement('export');

$docFinder = new Opus_DocumentFinder();

foreach ($docFinder->ids() as $id) {

    $doc = null;
    try {
        $doc = new Opus_Document($id);
    }
    catch (Opus_Model_NotFoundException $e) {
        // document with id $id does not exist
        continue;
    }

    $xmlModelOutput = new Opus_Model_Xml();
    $xmlModelOutput->setModel($doc);
    $xmlModelOutput->setStrategy(new Opus_Model_Xml_Version1());
    $xmlModelOutput->excludeEmptyFields();
    $domDocument = $xmlModelOutput->getDomDocument();
    $opusDocument = $domDocument->getElementsByTagName('Opus_Document')->item(0);
    $node = $opusDocuments->importNode($opusDocument, true);
    $export->appendChild($node);
}

$opusDocuments->appendChild($export);

$_exportFile= fopen($exportFile, 'w');
fputs($_exportFile, $opusDocuments->saveXML());
fclose($_exportFile);

exit();