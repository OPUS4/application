<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @copyright   Copyright (c) 2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * TODO is this script needed for performance reasons?
 * TODO convert into class for use as Command
 */

// TODO Why set to development?
define('APPLICATION_ENV', 'development');

// Bootstrapping
require_once dirname(__FILE__) . '/../common/bootstrap.php';

use Opus\Common\Document;
use Opus\Common\Repository;
use Opus\Model\Xml;
use Opus\Model\Xml\Version1;

$repository = Repository::getInstance();

$finder = $repository->getDocumentFinder();
$cache  = $repository->getDocumentXmlCache();

$docIds = $finder->setNotInXmlCache()->getIds();

echo 'Processing ' . count($docIds) . ' documents' . PHP_EOL;

foreach ($docIds as $docId) {
    $document = Document::get($docId);

    // xml version 1
    $omx = new Xml();
    $omx->setStrategy(new Version1())
        ->excludeEmptyFields()
        ->setModel($document)
        ->setXmlCache($cache);

    // TODO cache is updated as a side effect (that is not ideal and might not always be true)
    $omx->getDomDocument();

    echo "Cache refreshed for document #$docId\n";
}
