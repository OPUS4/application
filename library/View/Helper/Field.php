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
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Description of RenderForm
 *
 * @author Susanne Gottwald
 */
class View_Helper_Field extends Zend_View_Helper_Abstract {

    public $view;

    public function setView(Zend_View_Interface $view) {
        $this->view = $view;
    }

    public function scriptPath($script) {
        return $this->view->getScriptPath($script);
    }

    /**
     * method to render specific elements of an form
     * @param <type> $type element type that has to rendered
     * @param <type> $value value of element or Zend_Form_Element
     * @param <type> $name name of possible hidden element
     * @return element to render in view
     */
    public function field($type, $name, $value) {

        if ($name == null && $value == null) {
            $error_message = $this->view->translate('template_error_unknown_field');
            return "<br/><div style='width: 400px; color:red;'>" . $error_message . "</div><br/><br/>";
        } else {
            $method = "_render" . $type;
            if (method_exists($this, $method) === true) {
                $result = $this->$method($name, $value);
            } else {
                $result = $this->_renderGeneralElement($type, $value, $name);
            }
            return $result;
        }
    }

    /**
     *
     * @param <type> $name 
     */
    public function _renderSubmit($name, $value) {
        $submit = "\n\t\t<input type='submit' name='" . $name . "' id='" . $name . "' value='" . $value . "'/>";
        return $submit;
    }

    /**
     *
     * @param <type> $element
     */
    public function _renderElement($name, $element) {
        switch ($element["type"]) {
            case "Zend_Form_Element_Textarea" :
                $elementfield = "<table>\n\t<tr>\n\t\t<td>\n\t\t\t<label for='" . $element["id"] . "' class='" . $element["req"] . "'>" . $element["label"] . "</label>\n\t\t</td>\n\t\t";
                $elementfield .= "<td>\n\t\t\t<textarea cols='30' rows='9' name='" . $element["id"] . "' id='" . $element["id"] . "'>" . $element["value"] . "</textarea>\n\t\t</td>\n\t</tr>\n\t";
                $elementfield .= "<tr>\n\t\t<td colspan='2'>";
                if ($element["error"] != null) {
                    $elementfield .= "<ul class='errors'>";
                    foreach ($element["error"] AS $err)
                        $elementfield .= "\n\t\t\t<li>" . $err . "</li>";
                    $elementfield .= "\n\t\t</ul>";
                }
                $elementfield .= "</td>\n\t</tr>\n</table>\n\n";
                return $elementfield;

            case "Zend_Form_Element_Text" :
                $elementfield = "<table>\n\t<tr>\n\t\t<td>\n\t\t\t<label for='" . $element["id"] . "' class='" . $element["req"] . "'>" . $element["label"] . "</label>\n\t\t</td>\n\t\t";
                $elementfield .= "<td>\n\t\t\t<input type='text' name='" . $element["id"] . "' id='" . $element["id"] . "' value='" . $element["value"] . "' />\n\t\t</td>\n\t</tr>\n\t";
                $elementfield .= "<tr>\n\t\t<td colspan='2'>";
                if ($element["error"] != null) {
                    $elementfield .= "<ul class='errors'>";
                    foreach ($element["error"] AS $err)
                        $elementfield .= "\n\t\t\t<li>" . $err . "</li>";
                    $elementfield .= "\n\t\t</ul>";
                }
                $elementfield .= "</td>\n\t</tr>\n</table>\n\n";
                return $elementfield;

            case "Zend_Form_Element_Select":
                $elementfield = "<table>\n\t<tr>\n\t\t<td>\n\t\t\t<label for='" . $element["id"] . "' class='" . $element["req"] . "'>" . $element["label"] . "</label>\n\t\t</td>\n\t\t";
                $elementfield .= "<td>\n\t\t\t<select name='" . $element["id"] . "' id='" . $element["id"] . "'>\n\t\t\t\t";
                foreach ($element["options"] AS $key => $option) {
                    $elementfield .= "<option value='" . $key . "' label='". $option ."'";
                    if ($option === $element["value"] || $key === $element["value"])
                        $elementfield .= " selected='selected'>";
                    else
                        $elementfield .= ">";
                    $elementfield .= $option . "</option>\n\t\t\t\t";
                }
                $elementfield .= "</select></td>\n\t</tr>\n\t<tr>\n\t\t<td colspan='2'>";
                if ($element["error"] != null) {
                    $elementfield .= "<ul class='errors'>";
                    foreach ($element["error"] AS $err)
                        $elementfield .= "\n\t\t\t<li>" . $err . "</li>";
                    $elementfield .= "\n\t\t</ul>";
                }
                $elementfield .= "</td>\n\t</tr>\n</table>\n\n";
                return $elementfield;
        }
    }

    /**
     *
     * @param <type> $value
     * @param <type> $name
     * @return string 
     */
    public function _renderHidden($name, $value) {
        $hiddenfield = "<input type='hidden' name='" . $name . "' id='" . $name . "' value='" . $value . "' />";
        return $hiddenfield;
    }

    /**
     * Method to render a group of elements (group fields, buttons, hidden fields)
     * @param <Array> $group
     */
    public function _renderGroup($name, $group) {
        $fieldset = "";
        if (isset($group)) {
            $fieldset = "<fieldset>\n<legend>" . $this->view->translate($group['Name']) . "</legend>\n\t<table>";
            //show fields
            foreach ($group["Fields"] AS $field) {
                $fieldset .= "\n\t\t<tr>\n\t\t\t<td>";
                $fieldset .= "\n\t\t\t\t<label for='" . $field["id"] . "' class='" . $field["req"] . "'>" . $field["label"] . "</label>";
                $fieldset .= "\n\t\t\t</td>\n\t\t\t<td>";
                switch ($field['type']) {
                    case "Zend_Form_Element_Text":
                        $fieldset .= "\n\t\t\t\t<input type='text' name='" . $field["id"] . "' id='" . $field["id"] . "' value='" . $field["value"] . "' />";
                        break;

                    case "Zend_Form_Element_Textarea":
                        $fieldset .= "\n\t\t\t\t<textarea cols='30' rows='9' name='" . $field["id"] . "' id='" . $field["id"] . "'>" . $field["value"] . "</textarea>";
                        break;
                    case "Zend_Form_Element_Select" :
                        $fieldset .= "\n\t\t\t\t<select name='" . $field["id"] . "' id='" . $field["id"] . "'>\n\t\t\t\t\t";
                        foreach ($field["options"] AS $key => $option) {
                            $fieldset .= "<option value='" . $key . "' label='". $option ."'";
                            if ($option === $field["value"] || $key === $field["value"])
                                $fieldset .= " selected='selected'>";
                            else
                                $fieldset .= ">";
                            $fieldset .= $option . "</option>\n\t\t\t\t\t";
                        }
                        $fieldset .= "</select>";
                        break;
                    default:
                        throw new Application_Exception("Field Type {$field['type']} not found in View Helper.");
                }
                $fieldset .= "\n\t\t\t</td>\n\t\t</tr>\n\t\t<tr>\n\t\t\t<td colspan='2'>";
                if ($field["error"] != null) {
                    $fieldset .= "<ul class='errors'>";
                    foreach ($field["error"] AS $err)
                        $fieldset .= "<li>" . $err . "</li>";
                    $fieldset .= "</ul>";
                }
                $fieldset .= "</td>\n\t\t</tr>";
            }
            //show buttons
            foreach ($group["Buttons"] AS $button) {
                $fieldset .= "\n\t\t<tr>\n\t\t\t<td>";
                $fieldset .= "\n\t\t\t\t<input type='submit' name='" . $button["id"] . "' id='" . $button["id"] . "' value='" . $button["label"] . "' />";
                $fieldset .= "\n\t\t\t</td>\n\t\t</tr>";
            }
            //show hidden fields
            foreach ($group["Hiddens"] AS $hidden) {
                $fieldset .= "\n<input type='hidden' name='" . $hidden["id"] . "' id='" . $hidden["id"] . "' value='" . $hidden["value"] . "' />";
            }
            $fieldset .= "\n\t</table>\n</fieldset>\n\n";
        }
        return $fieldset;
    }

    /**
     *
     * @param <type> $type
     * @param <type> $value 
     */
    public function _renderGeneralElement($type, $value, $name) {
        echo "Sie wollen weder ein Element noch eine Group anzeigen lassen. <br>Der Fehler trat auf bei: ";
        echo print_r($value);
    }

}

?>
