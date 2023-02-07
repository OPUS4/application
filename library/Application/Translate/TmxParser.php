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
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_Translate_TmxParser
{
    /** @var resource */
    private $parser;

    /** @var array */
    private $translations;

    /** @var string */
    private $currentKey;

    /** @var string */
    private $currentModule;

    /** @var string */
    private $currentLang;

    /** @var bool */
    private $inSeg = false;

    /** @var string */
    private $currentValue;

    public function __construct()
    {
        $this->parser = xml_parser_create();

        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, "tagOpen", "tagClose");
        xml_set_character_data_handler($this->parser, "cdata");
        xml_set_default_handler($this->parser, "handleDefault");
    }

    public function __destruct()
    {
        xml_parser_free($this->parser);
        unset($this->parser);
    }

    /**
     * @param string $data
     * @return array
     */
    public function parse($data)
    {
        $this->translations = [];
        xml_parse($this->parser, $data);
        return $this->translations;
    }

    /**
     * @param resource $parser
     * @param string   $tag
     * @param array    $attributes
     */
    protected function tagOpen($parser, $tag, $attributes)
    {
        switch ($tag) {
            case 'TU':
                $this->currentKey                                = $attributes['TUID'];
                $this->translations[$this->currentKey]           = [];
                $this->translations[$this->currentKey]['values'] = [];
                if (isset($attributes['CREATIONTOOL'])) {
                    $this->currentModule                             = $attributes['CREATIONTOOL'];
                    $this->translations[$this->currentKey]['module'] = $this->currentModule;
                } else {
                    $this->currentModule = 'null';
                }
                break;

            case 'TUV':
                if (isset($attributes['XML:LANG'])) {
                    $this->currentLang = $attributes['XML:LANG'];
                } else {
                    $this->currentLang = null;
                    // TODO error handling
                }
                break;

            case 'SEG':
                xml_set_element_handler($this->parser, 'tagOpenSeg', 'tagCloseSeg');
                xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
                $this->inSeg = true;
                break;

            default:
                // do nothing
                break;
        }
    }

    /**
     * @param resource $parser
     * @param string   $tag
     */
    protected function tagClose($parser, $tag)
    {
        $tag = strtoupper($tag);
        switch ($tag) {
            case 'TU':
                $this->currentKey    = null;
                $this->currentModule = null;
                break;

            case 'TUV':
                $currentLang = null;
                break;

            default:
                // do nothing
                break;
        }
    }

    /**
     * Handles tags inside of SEG-elements.
     *
     * The tags inside of SEG-elements need to be rendered back
     * into the text.
     *
     * @param resource $parser
     * @param string   $tag
     * @param array    $attributes
     */
    protected function tagOpenSeg($parser, $tag, $attributes)
    {
        $value = "<$tag";

        foreach ($attributes as $name => $text) {
            $value .= " $name=\"$text\"";
        }

        $value .= '>';

        $this->currentValue .= $value;
    }

    /**
     * @param resource $parser
     * @param string   $tag
     */
    protected function tagCloseSeg($parser, $tag)
    {
        switch (strtoupper($tag)) {
            case 'SEG':
                xml_set_element_handler($this->parser, 'tagOpen', 'tagClose');
                xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 1);
                $this->translations[$this->currentKey]['values'][$this->currentLang] = $this->currentValue;
                $this->currentValue                                                  = null;
                $this->inSeg = false;
                break;

            default:
                $this->currentValue .= "</$tag>";
                break;
        }
    }

    /**
     * @param resource $parser
     * @param string   $cdata
     */
    protected function cdata($parser, $cdata)
    {
        if ($this->inSeg) {
            $this->currentValue .= $cdata;
        }
    }

    /**
     * Catches entities within values;
     *
     * @param resource $parser
     * @param string   $data
     */
    protected function handleDefault($parser, $data)
    {
        if ($this->inSeg) {
            $this->currentValue .= $data;
        }
    }
}
