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

/**
 * Simple class for reading, modifying and writing tmx files.
 *
 * This class is used for migrating custom TMX files into the database. It is also used for exporting and importing
 * translations from and to the database. This can be used to transfer customizations from one installation to another.
 *
 * A TMX file can normally only be part of one module. However in order to export/import translations that replace TMX
 * entries in multiple modules we are storing the module in the 'creationtool' attribute of the TU-elements. This way
 * we can import/export all custom translations in a single file without the need to create a ZIP package containing
 * separate TMX files for different modules.
 *
 * TODO functions to add/remove/update translations directly in a more direct way (not just passing in arrays)
 * TODO detect duplicate keys in file
 * TODO load should not add to existing keys - object should not represent multiple files
 * TODO needs to be able to read/write to/from TranslationManager array with additional information
 * TODO maybe a factory class for creating TMX document from TranslationManager output
 *
 * TODO store OPUSxxx something in the creationtool attribute of the header element
 * TODO create TMX file from string and write to sting
 */
class Application_Translate_TmxFile
{
    /**
     * template for new tmx files
     */

    public const TEMPLATE = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE tmx SYSTEM "http://www.gala-global.org/oscarStandards/tmx/tmx14.dtd">
<tmx version="1.4">
    <header creationtoolversion="1.0.0" datatype="winres" segtype="sentence" adminlang="en-us" srclang="de-de"
    o-tmf="abc" creationtool="OPUS4"></header>
    <body></body>
</tmx>';

    /**
     * Internal representation of the file
     *
     * TODO change internal storage to allow for additional attributes
     *
     * @var array
     */
    private $data = [];

    /** @var array */
    private $extraData = [];

    /**
     * Construct and optionally load TMX file.
     *
     * @param null|string $source (optional) full path of file to load
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
     * @return DOMDocument
     */
    public function getDomDocument()
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
     * @return $this Fluid Interface
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
     * @param string $fileName full path of file to load
     * @param bool   $addCdataWrapper
     * @return bool true on success or false on failure
     */
    public function load($fileName, $addCdataWrapper = true)
    {
        $tmx = file_get_contents($fileName);

        if ($addCdataWrapper) {
            $tmx = preg_replace('/<seg>(?!<!\[CDATA\[)/i', '<seg><![CDATA[', $tmx);
            $tmx = preg_replace('/(?<!]]>)<\/seg>/i', ']]></seg>', $tmx);
        }

        return $this->loadWithDom($tmx);
    }

    /**
     * @param string $tmx
     * @return bool|DOMDocument
     */
    protected function loadWithDom($tmx)
    {
        $dom                     = new DOMDocument();
        $dom->substituteEntities = false;
        $result                  = @$dom->loadXML($tmx); // supress warning since return value is checked
        if ($result) {
            $newData    = $this->domToArray($dom);
            $this->data = array_replace_recursive($this->data, $newData);
        }
        return $result;
    }

    /**
     * @param string $tmx
     * @return true
     */
    protected function loadWithParser($tmx)
    {
        $parser       = new Application_Translate_TmxParser();
        $translations = $parser->parse($tmx);

        /* foreach ($translations as $key => $data) {
            // TODO implement

        }*/

        return true;
    }

    /**
     * Save to file
     *
     * @param string $fileName Full path of file to save
     * @return bool true on success or false on failure
     */
    public function save($fileName)
    {
        $domDocument = $this->arrayToDom($this->data);
        return $domDocument->save($fileName) !== false;
    }

    /**
     * Set a segment value for the given translation.
     * If either the unit or the translation is not yet set, it will be added.
     *
     * @param string      $key Key identifier of translation
     * @param string      $language Language identifier of translation
     * @param string      $text Value to set for translation
     * @param null|string $module
     * @return $this fluent Interface
     */
    public function setTranslation($key, $language, $text, $module = null)
    {
        $tmxArray = $this->toArray();

        if (! isset($tmxArray[$key])) {
            $tmxArray[$key] = [];
        }

        $tmxArray[$key][$language] = $text;

        $this->fromArray($tmxArray);

        if ($module !== null) {
            $this->setModuleForKey($key, $module);
        }

        return $this;
    }

    /**
     * Checks if a translation exists.
     *
     * @param string      $key Translation key
     * @param string|null $language Optionally what language to look for
     * @return bool
     */
    public function hasTranslation($key, $language = null)
    {
        if ($language === null) {
            return isset($this->data[$key]);
        } else {
            return isset($this->data[$key][$language]);
        }
    }

    /**
     * Finds translations that contain needle.
     *
     * TODO find by key?
     * TODO find by content?
     *
     * @param string      $needle
     * @param string|null $language
     */
    public function findTranslation($needle, $language = null)
    {
    }

    /**
     * Returns languages included in file.
     */
    public function getLanguages()
    {
        // TODO implement
    }

    /**
     * Removes entry from translation file.
     *
     * @param string $key Translation key
     */
    public function removeTranslation($key)
    {
        unset($this->data[$key]);
    }

    /**
     * @param string $key
     * @param string $module
     */
    public function setModuleForKey($key, $module)
    {
        if (! is_array($this->extraData)) {
            $this->extraData = [];
        }

        if (! isset($this->extraData[$key])) {
            $this->extraData[$key] = [];
        }

        $this->extraData[$key]['module'] = $module;
    }

    /**
     * @param string $key
     * @return array|null
     */
    public function getModuleForKey($key)
    {
        if (isset($this->extraData[$key]['module'])) {
            return $this->extraData[$key]['module'];
        }

        return null;
    }

    /**
     * Converts TMX DOM document to array structure.
     *
     * @param DOMDocument $domDocument
     * @return array
     */
    protected function domToArray($domDocument)
    {
        $tuElements = $domDocument->getElementsByTagName('tu');

        $translationUnits = [];

        foreach ($tuElements as $tu) {
            $key                    = $tu->attributes->getNamedItem('tuid')->textContent;
            $translationUnits[$key] = [];

            $module = $tu->attributes->getNamedItem('creationtool');
            if ($module !== null) {
                $this->setModuleForKey($key, $module->nodeValue);
            }

            // process language entries (TUV-elements)
            foreach ($tu->getElementsByTagName('tuv') as $child) {
                $lang                          = $child->attributes->getNamedItem('lang')->nodeValue;
                $translation                   = $child->getElementsByTagName('seg')->item(0)->nodeValue;
                $translationUnits[$key][$lang] = $translation;
            }
        }
        return $translationUnits;
    }

    /**
     * Converts array structure to TMX DOM document.
     *
     * @param array $array
     * @return DOMDocument
     */
    protected function arrayToDom($array)
    {
        $dom = new DOMDocument();

        $dom->preserveWhiteSpace = false;
        $dom->formatOutput       = true;
        $dom->substituteEntities = false;

        $dom->loadXML(self::TEMPLATE);

        foreach ($array as $unitName => $variants) {
            $module = $this->getModuleForKey($unitName);

            $tuElement = $dom->createElement('tu');
            $tuElement->setAttribute('tuid', $unitName);
            if ($module !== null) {
                $tuElement->setAttribute('creationtool', $module);
            }
            $bodyElement = $dom->getElementsByTagName('body')->item(0);
            $tuNode      = $bodyElement->appendChild($tuElement);
            foreach ($variants as $lang => $text) {
                $tuvElement = $dom->createElement('tuv');
                $tuvElement->setAttribute('xml:lang', $lang);
                $segElement = $dom->createElement('seg');
                $tuvNode    = $tuNode->appendChild($tuvElement);
                $segNode    = $tuvNode->appendChild($segElement);
                $textNode   = $dom->createCDATASection($text);
                $segNode->appendChild($textNode);
            }
        }
        return $dom;
    }
}
