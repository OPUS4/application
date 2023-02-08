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

if (basename(__FILE__) !== basename($argv[0])) {
    echo "script must be executed directy (not via opus-console)\n";
    exit;
}

require_once dirname(__FILE__) . '/../common/bootstrap.php';

$options = getopt('', ['source:', 'schema-cache:']);

if (! isset($options['source']) || empty($options['schema-cache'])) {
    echo "Usage: {$argv[0]} --source <source url or filename> --schema-cache <path to schema files>\n";
    exit;
}

libxml_use_internal_errors(true);

$sourceXml      = file_get_contents($options['source']);
$sourceDocument = new DOMDocument();
$sourceDocument->loadXML($sourceXml);

$xpath = new DOMXPath($sourceDocument);
$xpath->registerNamespace('oai', "http://www.openarchives.org/OAI/2.0/");
$xpath->registerNamespace('oai_dc', "http://www.openarchives.org/OAI/2.0/oai_dc/");
$xpath->registerNamespace('cc', "http://www.d-nb.de/standards/cc/");
$xpath->registerNamespace('dc', "http://purl.org/dc/elements/1.1/");
$xpath->registerNamespace('ddb', "http://www.d-nb.de/standards/ddb/");
$xpath->registerNamespace('pc', "http://www.d-nb.de/standards/pc/");
$xpath->registerNamespace('xMetaDiss', "http://www.d-nb.de/standards/xmetadissplus/");
$xpath->registerNamespace('epicur', "urn:nbn:de:1111-2004033116");
$xpath->registerNamespace('dcterms', "http://purl.org/dc/terms/");
$xpath->registerNamespace('thesis', "http://www.ndltd.org/standards/metadata/etdms/1.0/");

$xMetaDissNodes = $xpath->query('//xMetaDiss:xMetaDiss');

if (! $xMetaDissNodes instanceof DOMNodeList || $xMetaDissNodes->length === 0) {
    echo "No Document found.";
}
$xMetaDissNode = $xMetaDissNodes->item(0);
//foreach ($xMetaDissNodes as $xMetaDissNode) {
$metadataDocument = new DOMDocument();
$importedNode     = $metadataDocument->importNode($xMetaDissNode, true);
$metadataDocument->appendChild($importedNode);

$schemaFile = realpath($options['schema-cache'] . '/xmetadissplus.xsd');
if (! is_file($schemaFile)) {
    echo "Could not find schema file '" . $options['schema-cache'] . '/xmetadissplus.xsd' . "'";
}
$metadataDocument->schemaValidate($options['schema-cache'] . '/xmetadissplus.xsd');
printXmlErrors($sourceXml);

/**
 * @param string $xml
 */
function printXmlErrors($xml)
{
    $errors = libxml_get_errors();

    foreach ($errors as $error) {
        if ($error->level < 2) {
            continue;
        }

        $lines = explode("\n", $xml);
        $line  = $lines[abs(($error->line) - 1)];
        echo "\n\nERROR(" . $error->level . "): \n";
        echo "\t" . trim($error->message) . ' at line ' . $error->line . ":\n";
        for ($i = $error->line - 20; $i < $error->line; $i++) {
            echo isset($lines[$i]) ? ($i + 1) . "\t" . $lines[$i] . "\n" : '';
        }
    }
}
