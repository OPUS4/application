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
 * @package     Module_Publish
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Publish_View_Helper_Fieldset extends Zend_View_Helper_Abstract {

    protected $_disable = false;

    function fieldset() {
        
    }
     
    function renderHtmlText($field, $options) {
        $fieldset = "";
        if (!isset($field['isLeaf'])) {
            $fieldset .= "\n\t\t\t\t<input type='text' class='form-textfield' name='" . $field['id'] . "' id='"
                . $field['id'] . "' ";
            if ($options !== null) {
                $fieldset .= $options . " ";
            }
            else {
                $fieldset .= "size='30' ";
            }

            if ($field['disabled'] === true) {
                $fieldset .= " disabled='1' ";
                $this->_disable = true;
            }

            $fieldset .= " title='" . htmlspecialchars($this->view->translate($field['hint']), ENT_QUOTES) . "' ";
            
            $fieldset .= " value='" . htmlspecialchars($field['value'], ENT_QUOTES) . "' />\n";            
            if (isset($field['desc'])) {
                $fieldset .= '<div class="description hint">' . $this->view->translate($field['desc']) . '</div>';
            }
        }
        return $fieldset;
    }

    function renderHtmlTextarea($field, $options) {
        $fieldset = "\n\t\t\t\t<textarea name='" . $field['id'] . "' class='form-textarea' ";
        if ($options !== null) {
            $fieldset .= $options . " ";
        }
        else {
            $fieldset .= "cols='30' rows='5' ";
        }

        if ($field['disabled'] === true) {
            $fieldset .= " disabled='1' ";
            $this->_disable = true;
        }

        $fieldset .= " title='" . htmlspecialchars($this->view->translate($field['hint']), ENT_QUOTES) . "' ";
        $fieldset .= " id='" . $field['id'] . "'>" . htmlspecialchars($field['value'], ENT_QUOTES) . "</textarea>";

        return $fieldset;
    }

    // TODO: wofür wird der Parameter $options benötigt?
    function renderHtmlSelect($field, $options) {
        // TODO move style to CSS
        $fieldset = "\n\t\t\t\t" . '<select style="width:300px" name="' . $field['id']
            . '" class="form-selectfield"  id="' . $field['id'] . '"';
        $fieldset .= ' title="' . htmlspecialchars($this->view->translate($field['hint']), ENT_QUOTES) . '"';
        if ($field['disabled'] === true) {
            $fieldset .= ' disabled="1" ';
            // TODO warum wird hier nicht auch $this->disable = true gesetzt?
        }
        $fieldset .= '>' . "\n\t\t\t\t\t";

        foreach ($field['options'] AS $key => $option) {
            $fieldset .= '<option value="' . htmlspecialchars($key, ENT_QUOTES) . '" label="'
                . htmlspecialchars($option, ENT_QUOTES) . '"';
            
            if ($option == $field['value'] || $key == $field['value']) {
                $fieldset .= ' selected="selected"';
            }

            $fieldset .= '>';
            $fieldset .= htmlspecialchars($option, ENT_QUOTES) . '</option>' . "\n\t\t\t\t\t";
        }
        $fieldset .= '</select>' . "\n";

        if (isset($field['desc'])) {
            $fieldset .= '<div class="description hint">' . $this->view->translate($field['desc']) . '</div>';
        }
        return $fieldset;
    }

    // TODO: wofür wird der Parameter $options benötigt?
    function renderHtmlCheckbox($field, $options) {
        $fieldset = "<input type='hidden' name='" . $field['id'] . "' value='0' />";
        $fieldset .= "\n\t\t\t\t<input type='checkbox' class='form-checkbox' name='" . $field['id'] . "' id='"
            . $field['id'] . "' ";
        $fieldset .= "value='" . $field['value'] . "' ";
        if ($field['check'] == 'checked') {
            $fieldset .= " checked='checked' "; 
        }

        if ($field['disabled'] === true) {
            $fieldset .= " disabled='disabled' ";
            $this->_disable = true;
        }

        $fieldset .= " />\n";

        return $fieldset;
    }

    function renderHtmlFile($field, $options) {
        $fieldset = "<input type='file' name='" . $field['id'] . "' id='" . $field['id']
            . "' enctype='multipart/form-data' ";
        $fieldset .= "title='" . htmlspecialchars($this->view->translate($field['hint']), ENT_QUOTES) . "' ";
        if ($options !== null) {
            $fieldset .= $options . " ";
        }
        else {
            $fieldset .= "size='30'";
        }
        $fieldset .= " />\n";

        return $fieldset;
    }

    /**
     * TODO:
     * Fehlerursache: multiplicity > 1 (Gruppe) aber Aufruf mit element
     * Wenn element aufgerufen wird, es sich aber um eine Gruppe handelt, wird hier eine Exception geworfen und die
     * Gruppe nicht gerendert.
     * Kann man verhindern, wenn man dem Array ein zusätzliches Feld "isGroup" o.ä. mitgibt, das überprüft, ob es
     * sich tatsächlich um eine Gruppe handelt.
     */
    function renderFieldsetErrors($field) {
        $fieldset = "";
        if (isset($field) && !empty($field)) {
            $fieldset .= "<div class='form-errors'><ul>";
            foreach ($field AS $err) {
                $fieldset .= "\n<li>" . htmlspecialchars($err, ENT_QUOTES) . "</li>";
            }
            $fieldset .= "\n</ul></div>";
        }
        return $fieldset;
    }

    function renderHtmlButtons($buttons) {
        $fieldset = "\n\t\t\t\t<div class='button-wrapper add-delete-wrapper'>";
        foreach ($buttons AS $button) {
            if (!$this->_disable) {
                $fieldset .= "<input type='submit' ";
                if (strstr($button['id'], 'Down') !== false) {
                    $fieldset .= "class='form-button down-button' "; 
                }
                else {
                    if (strstr($button['id'], 'Up') !== false) {
                        $fieldset .= "class='form-button up-button' "; 
                    }
                }

                if (strstr($button['id'], 'add') !== false) {
                    $fieldset .= "class='form-button add-button' "; 
                }
                else {
                    if (strstr($button['id'], 'delete') !== false) {
                        $fieldset .= "class='form-button delete-button' "; 
                    }
                }

                $fieldset .= "name='" . $button['id'] . "' id='" . $button['id'] . "' value='"
                    . htmlspecialchars($button['label'], ENT_QUOTES) . "' />&nbsp;";
            }
        }
        $fieldset .= "</div>";

        $this->_disable = false;
        return $fieldset;
    }

    function renderHtmlHidden($hiddens) {
    $fieldset = "";
        foreach ($hiddens AS $hidden) {
            $fieldset .= "\n<input type='hidden' name='" . $hidden['id'] . "' id='" . $hidden['id'] . "' value='"
                . $hidden['value'] . "' />";
        }    
        return $fieldset;
    }
    
    function _renderSubmit($value, $options=null, $name=null) {
        $submit = "\n\t\t<input type='submit' name='" . $name . "' id='" . $name . "' value='"
            . htmlspecialchars($this->view->translate($value), ENT_QUOTES) . "' ";
        if (isset($options)) {
            $submit .= $options . " />";
        }
        return $submit;
    }

    // TODO: wofür wird der Parameter $options benötigt?
    function _renderHidden($value, $options = null, $name=null) {
        $hiddenfield = "<input type='hidden' name='" . $name . "' id='" . $name . "' value='" . $value . "' />";
        return $hiddenfield;
    }

    /**
     * Returns the hint string for a required element
     * @return <type> 
     */
    function getRequiredSign() {
        return "<span class='required' title='"
            . htmlspecialchars($this->view->translate('required_star_title'), ENT_QUOTES) . "'>*</span>";
    }

    /**
     * Returns a html String for displaying the group hint
     * @param <String> Name of element or group
     * @return <type>
     */
    function getFieldsetHint($name) {
        return "<div class='description hint'><p>" . $this->view->translate('hint_' . $name) . "</p></div>";
    }

    function getLabelFor($name, $label, $required) {
        $fieldset = "<label for='" . $name . "'>" . htmlspecialchars($this->view->translate($label), ENT_QUOTES);

        if ($required === 'required') {
            $fieldset .= $this->getRequiredSign();
        }

        $fieldset .= "</label>";
        return $fieldset;
    }

    function getLegendFor($name) {
        return "<legend>" . $this->view->translate($name) . "</legend>\n\t\t\n\t\t";
    }

}

