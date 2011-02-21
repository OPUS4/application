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
 * @package     Module_Import
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 *
 */
class MatheonMigration_Base {

    /**
     * Load XML document
     */
    protected function load_xml_mysqldump($filename) {
        echo "Loading XML mysqldump '$filename'...\n";

        $xml = new DOMDocument();
        if (true !== $xml->load($filename)) {
            throw new Exception("Cannot load XML document $filename");
        }

        $xml_resultsets = $xml->getElementsByTagName('resultset');
        $xml_resultset = $xml_resultsets->item(0);

        if (is_null($xml_resultset)) {
            throw new Exception("Cannot parse XML document $filename: no resultset element.");
        }

        $statement = $xml_resultset->getAttribute('statement');
        echo "Found statement: " . $statement . "\n";

        $data = array();
        foreach ($xml_resultset->childNodes as $row) {
            if ($row instanceof DOMElement) {
                // $node_xpath = $row->getNodePath();
                // $node_name = $row->tagName;
                // echo "$node_name\n";

                $row_data = array();
                foreach ($row->childNodes AS $column) {
                    if ($column instanceof DOMElement) {
                        if ($column->tagName === 'field') {
                            $key = $column->getAttribute('name');
                            $textValue = (is_null($column->textContent) ? '' : $column->textContent);
                            $row_data[$key] = trim($textValue);
                        }
                    }
                }
                $data[] = $row_data;
            }
        }

        return $data;
    }

    protected static function array2hash($array, $hash_field) {
        $hash = array();
        foreach ($array as $element) {
            $id = $element[$hash_field];
            if (false === array_key_exists($id, $hash)) {
                $hash[$id] = array();
            }
            $hash[$id][] = $element;
        }

        return $hash;
    }
}
