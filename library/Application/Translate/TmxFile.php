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
 * @author      Edouard Simon <edouard.simon@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Simple class for reading, modifying and writing tmx files.
 *
 * TODO functions to add/remove/update translations directly in a more direct way (not just passing in arrays)
 * TODO detect duplicate keys in file
 * TODO load should not add to existing keys - object should not represent multiple files
 * TODO use creationtool on entries to store module name
 */
class Application_Translate_TmxFile
{
    /**
     * template for new tmx files
     */

    const TEMPLATE = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE tmx SYSTEM "http://www.gala-global.org/oscarStandards/tmx/tmx14.dtd">
<tmx version="1.4">
    <header creationtoolversion="1.0.0" datatype="winres" segtype="sentence" adminlang="en-us" srclang="de-de"
    o-tmf="abc" creationtool="Opus4"></header>
    <body></body>
</tmx>';

    /**
     * Internal representation of the file
     */
    private $data = [];

    /**
     * Construct and optionally load TMX file.
     *
     * @param string $source (optional) full path of file to load
     *                        if no source is provided, an empty file is created.
     */
    public function __construct($source = null)
    {
        if (is_string($source)) {
            $this->load($source);
        }
    }

    /**
     * Export as DomDocument object.
     *
     * @return DomDocument
     */
    public function toDomDocument()
    {
        return $this->arrayToDom($this->data);
    }

    /**
     * Export as array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Import translation entries from array.
     *
     * This method may be called multiple times. If keys exist in more than one array, the last key overwrites the
     * previously imported.
     *
     * @param array $array
     * @return self Fluid Interface
     */
    public function fromArray($array)
    {
        $this->data = array_replace_recursive($this->data, $array);
        return $this;
    }

    /**
     * Load from file. This method may be called
     * multiple times. If keys exist in more than
     * one file, the last key overwrites the previously loaded.
     *
     * @param $fileName full path of file to load
     * @return bool true on success or false on failure
     */
    public function load($fileName)
    {
        $dom = new DOMDocument();
        $dom->substituteEntities = false;
        $result = @$dom->load($fileName); // supress warning since return value is checked
        if ($result) {
            $newData = $this->domToArray($dom);
            $this->data = array_replace_recursive($this->data, $newData);
        }
        return $result;
    }

    /**
     * Save to file
     *
     * @param $fileName string Full path of file to save
     * @return bool true on success or false on failure
     */
    public function save($fileName)
    {
        $domDocument = $this->arrayToDom($this->data);
        return ($domDocument->save($fileName) !== false);
    }

    /**
     * Set a segment value for the given translation unit variant.
     * If either the unit or the variant is not yet set, it will be added.
     *
     * @param string $key identifier of translation unit
     * @param string $language identifier of variant
     * @param string $text Segment value to set for translation unit variant
     *
     * @return self fluent Interface
     */
    public function setTranslation($key, $language, $text)
    {
        $tmxArray = $this->toArray();

        if (! isset($tmxArray[$key])) {
            $tmxArray[$key] = [];
        }

        $tmxArray[$key][$language] = $text;

        $this->fromArray($tmxArray);

        return $this;
    }

    /**
     * Checks if a translation exists.
     *
     * @param $key Translation key
     * @param null $language Optionally what language to look for
     * @return bool
     */
    public function hasTranslation($key, $language = null)
    {
        if (is_null($language)) {
            return isset($this->data[$key]);
        } else {
            return isset($this->data[$key][$language]);
        }
    }

    /**
     * Removes entry from translation file.
     *
     * @param $key string Translation key
     */
    public function removeTranslation($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Converts TMX DOM document to array structure.
     *
     * @param $domDocument
     * @return array
     */
    protected function domToArray($domDocument)
    {
        $tuElements = $domDocument->getElementsByTagName('tu');

        $translationUnits = [];

        foreach ($tuElements as $tu) {
            $key = $tu->attributes->getNamedItem('tuid')->textContent;
            $translationUnits[$key] = [];
            foreach ($tu->getElementsByTagName('tuv') as $child) {
                $translationUnits[$key][$child->attributes->getNamedItem('lang')->nodeValue] =
                    $child->getElementsByTagName('seg')->item(0)->nodeValue;
            }
        }
        return $translationUnits;
    }

    /**
     * Converts array structure to TMX DOM document.
     *
     * @param $array
     * @return DOMDocument
     */
    protected function arrayToDom($array)
    {
        $dom = new DOMDocument();

        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->substituteEntities = false;

        $dom->loadXML(self::TEMPLATE);

        foreach ($array as $unitName => $variants) {
            $tuElement = $dom->createElement('tu');
            $tuElement->setAttribute('tuid', $unitName);
            $bodyElement = $dom->getElementsByTagName('body')->item(0);
            $tuNode = $bodyElement->appendChild($tuElement);
            foreach ($variants as $lang => $text) {
                $tuvElement = $dom->createElement('tuv');
                $tuvElement->setAttribute('xml:lang', $lang);
                $segElement = $dom->createElement('seg');
                $tuvNode = $tuNode->appendChild($tuvElement);
                $segNode = $tuvNode->appendChild($segElement);
                $textNode = $dom->createCDATASection($text);
                $segNode->appendChild($textNode);
            }
        }
        return $dom;
    }
}
