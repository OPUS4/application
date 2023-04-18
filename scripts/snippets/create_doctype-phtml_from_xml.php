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
 * @copyright   Copyright (c) 2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * This script takes a doctype XML-definition as input and spills out the
 * PHP instructions for use in the corresponding .phtml file.
 * It requires the file doctype.xslt to be in the same directory as this script.
 *
 * TODO move (is used for development and probably only of limited use)
 */

if ($argc === 2) {
    $filename = realpath($argv[1]);
    if (! is_file($filename)) {
        echo "Could not find file {$argv[1]} ($filename)";
        exit;
    }
} else {
    echo "No file supplied";
    exit;
}
$xml = new DOMDocument();
$xml->load($filename);
$xslt = new DOMDocument();
$xslt->load(dirname(__FILE__) . "/doctype.xslt");
$proc = new XSLTProcessor();
$proc->importStyleSheet($xslt);
$proc->transformToURI($xml, 'php://output');
