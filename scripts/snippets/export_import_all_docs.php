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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Document;
use Opus\Common\Model\NotFoundException;
use Opus\Common\Repository;
use Opus\Model\Xml;
use Opus\Model\Xml\Version1;

/**
 * Tries to export and import all documents.
 *
 * TODO move (this is a test script)
 */

$docFinder = Repository::getInstance()->getDocumentFinder();

foreach ($docFinder->getIds() as $id) {
    $doc = null;
    try {
        $doc = Document::get($id);
    } catch (NotFoundException $e) {
        // document with id $id does not exist
        continue;
    }

    echo "try to export document $id ... ";
    $xmlModelOutput = new Xml();
    $xmlModelOutput->setModel($doc);
    $xmlModelOutput->setStrategy(new Version1());
    $xmlModelOutput->excludeEmptyFields();
    $domDocument = $xmlModelOutput->getDomDocument();
    echo "export of document $id was successful.\n";

    echo "try to import document based on the exported dom tree ... ";
    $xmlModelImport = new Xml();
    $xmlModelImport->setStrategy(new Version1());
    $xmlModelImport->setXml($domDocument->saveXML());
    try {
        $doc = $xmlModelImport->getModel();
        $doc->store();
        echo "OK - import of document $id was successful.\n";
    } catch (Exception $e) {
        echo "ERR - import of document $id was NOT successful.\n";
        echo $e;
    }
    echo "\n\n";
}

exit();
