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
 * @copyright   Copyright (c) 2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * The code here was written using the cod of Zend_View_Helper_FormRadio as
 * template, adapting it to the specific needs of displaying multiple input
 * elements label for the supported languages.
 */
class Application_View_Helper_FormTranslation extends Zend_View_Helper_FormRadio
{
    /**
     * @var string
     * @phpcs:disable
     */
    protected $_inputType = 'text';
    // @phpcs:enable

    /**
     * @param string     $name
     * @param mixed|null $value
     * @param array|null $attribs
     * @param array|null $options
     * @param string     $listsep
     * @return string
     * @throws Zend_Filter_Exception
     */
    public function formTranslation($name, $value = null, $attribs = null, $options = null, $listsep = "<br />\n")
    {
        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        // @phpcs:disable
        extract($info);
        // @phpcs:enable

        // retrieve attributes for labels (prefixed with 'label_' or 'label')
        if ($attribs === null) {
            $attribs = [];
        }

        $labelAttribs = [];
        foreach ($attribs as $key => $val) {
            $tmp    = false;
            $keyLen = strlen($key);
            if ((6 < $keyLen) && (substr($key, 0, 6) === 'label_')) {
                $tmp = substr($key, 6);
            } elseif ((5 < $keyLen) && (substr($key, 0, 5) === 'label')) {
                $tmp = substr($key, 5);
            }

            if ($tmp) {
                // make sure first char is lowercase
                $tmp[0]             = strtolower($tmp[0]);
                $labelAttribs[$tmp] = $val;
                unset($attribs[$key]);
            }
        }

        if (isset($attribs['textarea']) && $attribs['textarea']) {
            $this->_inputType = 'textarea';
        }
        unset($attribs['textarea']);

        $list = [];

        $pattern = @preg_match('/\pL/u', 'a')
            ? '/[^\p{L}\p{N}\-\_]/u'    // Unicode
            : '/[^a-zA-Z0-9\-\_]/';     // No Unicode

        $filter = new Zend_Filter_PregReplace($pattern, '');

        $translate = Application_Translate::getInstance();

        $name = $this->view->escape($name);

        if ($options === null) {
            $options = [];
        }

        if (isset($textarea) && $textarea) {
            $this->_inputType = 'textarea';
        }

        foreach ($options as $label => $value) {
            $disabled = null;

            $optId = "$id-{$filter->filter($label)}";

            // is it disabled?
            $disabled = '';
            if (true === $disable) {
                $disabled = ' disabled="disabled"';
            } elseif (is_array($disable) && in_array($value, $disable)) {
                $disabled = ' disabled="disabled"';
            }

            $item = '<label' . $this->_htmlAttribs($labelAttribs) . '>'
                . "<span>{$translate->translateLanguage($label)}</span>";

            if ($this->_inputType !== 'textarea') {
                $item .= "<input type=\"{$this->_inputType}\""
                    . " name=\"{$name}[$label]\""
                    . " id=\"$optId\""
                    . " value=\"{$this->view->escape($value)}\""
                    . $disabled
                    . $this->_htmlAttribs($attribs)
                    . $this->getClosingBracket();
            } else {
                $item .= '<textarea'
                    . " name=\"{$name}[$label]\""
                    . " id=\"$optId\""
                    . $disabled
                    . $this->_htmlAttribs($attribs) . '>'
                    . $value
                    . '</textarea>';
            }

            $item .= '</label>';

            $list[] = $item;
        }

        return implode($listsep, $list);
    }
}
