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
 * @package     Module_Setup
 * @author      Edouard Simon (edouard.simon@zib.de)
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * 
 */
class Util_TmxFile {

    /**
     * template for new tmx files
     */
    const template =
            <<<'EOT'
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE tmx SYSTEM "http://www.gala-global.org/oscarStandards/tmx/tmx14.dtd">
<tmx version="1.4">
    <header creationtoolversion="1.0.0" datatype="winres" segtype="sentence" adminlang="en-us" srclang="de-de" o-tmf="abc" creationtool="Opus4"></header>
    <body></body>
</tmx>
EOT;

    /**
     * Internal representation of the file
     */
    protected $dom;

    
    /**
     * Simple class for reading, modifiying and writing tmx files.
     * 
     * @param string $source (optional) full path of file to load
     *                        if no source is provided, an empty file is created.
     * 
     */
    public function __construct($source = null) {
        $this->dom = new DOMDocument();
        if (is_string($source)) {
            $this->load($source);
        } else {
            $this->_initDocument();
        }
    }

    /**
     * Initialize empty document.
     */
    protected function _initDocument() {
        $this->dom->loadXML(self::template);
    }

    /**
     * Export as DomDocument object.
     * 
     * @return DomDocument
     */
    public function toDomDocument() {
        return $this->dom;
    }

    /**
     * Export as array.
     * 
     * @return array 
     */
    public function toArray() {
        $tuElements = $this->dom->getElementsByTagName('tu');
        $translationUnits = array();
        foreach ($tuElements as $tu) {
            $key = $tu->attributes->getNamedItem('tuid')->nodeValue;
            $translationUnits[$key] = array();
            foreach ($tu->getElementsByTagName('tuv') as $child) {
                $translationUnits[$key][$child->attributes->getNamedItem('lang')->nodeValue] = $child->getElementsByTagName('seg')->item(0)->nodeValue;
            }
        }
        return $translationUnits;
    }

    
    /**
     * Import from array.
     * 
     * @param array $array
     * @return self Fluid Interface
     */
    public function fromArray($array) {
        $this->_initDocument();
        foreach($array as $unitName => $variants) {
            $tuElement = $this->dom->createElement('tu');
            $tuElement->setAttribute('tuid', $unitName);
            $bodyElement = $this->dom->getElementsByTagName('body')->item(0);
            $tuNode = $bodyElement->appendChild($tuElement);
            foreach($variants as $lang => $text) {
                $tuvElement = $this->dom->createElement('tuv');
                $tuvElement->setAttribute('xml:lang', $lang);
                $segElement = $this->dom->createElement('seg');
                $tuvNode = $tuNode->appendChild($tuvElement);
                $segNode = $tuvNode->appendChild($segElement);
                $segNode->nodeValue = $text;
            }
        }
        return $this;
    }

    /**
     * Load from file
     * 
     * @param $fileName full path of file to load
     * @return bool true on success or false on failure
     */
    public function load($fileName) {
        return $this->dom->load($fileName);
    }

    
    /**
     * Save to file
     * 
     * @param $fileName full path of file to save
     * @return bool true on success or false on failure
     */
    public function save($fileName) {
        return ($this->dom->save($fileName) !== false);
    }

    /**
     * Set a segment value for the given translation unit variant.
     * If either the unit or the variant is not yet set, it will be added.
     * 
     * @param string $unitName identifier of translation unit
     * @param string $language identifier of variant
     * @param string $text Segment value to set for translation unit variant
     * 
     * @return self Fluid Interface
     */
    public function setVariantSegment($unitName, $language, $text) {
        $tmxArray = $this->toArray();
        if (!isset($tmxArray[$unitName]))
            $tmxArray[$unitName] = array();
        $tmxArray[$unitName][$language] = $text;
        $this->fromArray($tmxArray);
        return $this;
    }
}
