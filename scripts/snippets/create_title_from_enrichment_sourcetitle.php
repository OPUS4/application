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
 * @author      Edouard Simon (edouard.simon@zib.de)
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: update-thesispublisher.php 11775 2013-06-25 14:28:41Z tklein $
 */
/**
 * 
 */
if (basename(__FILE__) !== basename($argv[0])) {
    echo "script must be executed directy (not via opus-console)\n";
    exit;
}

require_once dirname(__FILE__) . '/../common/bootstrap.php';

$options = getopt('', array('dryrun', 'type:'));

$dryrun = isset($options['dryrun']);

if(!isset($options['type']) || empty($options['type'])) {
    echo "Usage: {$argv[0]} --type <type of title> (--dryrun)\n";
    echo "type of title must be provided (e. g. source, parent).\n";
    exit;
}

$getType = 'getTitle'.ucfirst(strtolower($options['type']));
$addType = 'addTitle'.ucfirst(strtolower($options['type']));

if ($dryrun)
    _log("TEST RUN: NO DATA WILL BE MODIFIED");

$docFinder = new Opus_DocumentFinder();
$docIds = $docFinder->setEnrichmentKeyExists('SourceTitle')->ids();

_log(count($docIds) . " documents found");

foreach ($docIds as $docId) {
    $doc = new Opus_Document($docId);
    $enrichments = $doc->getEnrichment();
    foreach ($enrichments as $enrichment) {
        $enrichmentArray = $enrichment->toArray();
        if ($enrichmentArray['KeyName'] == 'SourceTitle') {
            $sourceTitles = $doc->{$getType}();
            $titleExists = false;
            foreach ($sourceTitles as $sourceTitle) {
                if ($sourceTitle->getValue() == $enrichmentArray['Value']) {
                    $titleExists = true;
                    _log('TitleSource already exists for Document #' . $docId . '. Skipping.. ');
                    break;
                }
            }
            if (!$titleExists) {
                $titleSource = $doc->{$addType}();
                $titleSource->setValue($enrichmentArray['Value']);
                if (!$dryrun)
                    $doc->store();
                _log('Document #' . $docId . ' updated');
            }
        }
    }
}

function _log($message) {
    echo "$message\n";
}

?>
