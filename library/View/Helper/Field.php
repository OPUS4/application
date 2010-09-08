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
     * @param <type> $element
     */
    public function _renderElement($name, $element) {
        $elementfield = "<fieldset class='fieldset'><legend class='legend'>" . $this->view->translate($element["label"]) . "</legend>\n\t\t\n\t\t";
        $elementfield .= "<table width='100%' class='table'>\n\t<tr>\n\t\t<td width='25%'>\n\t\t\t";
        $elementfield .= "<label for='" . $element["id"] . "'>" . $element["label"] . "</label></td><td>";

        switch ($element["type"]) {
            case "Zend_Form_Element_Textarea" :
                $elementfield .= "\n\t\t\t<textarea cols='30' rows='9' name='" . $element["id"] . "' id='" . $element["id"] . "' ";
                $elementfield .= " title='" . $this->view->translate($element["hint"]) . "'>";
                $elementfield .=  $element["value"] . "</textarea>";

                if ($element["req"] === 'required')
                    $elementfield .= $this->getRequiredSign();
                $elementfield .= "\n\t\t</td>\n\t</tr>\n\t";
                $elementfield .= "<tr>\n\t\t<td colspan='2'>";

                if ($element["error"] != null) {
                    $elementfield .= "<ul class='errors'>";
                    foreach ($element["error"] AS $err)
                        $elementfield .= "\n\t\t\t<li>" . $err . "</li>";
                    $elementfield .= "\n\t\t</ul>";
                }
                break;

            case "Zend_Form_Element_Text" :
                $elementfield .= "\n\t\t\t<input type='text' name='" . $element["id"] . "' id='" . $element["id"] . "'";
                $elementfield .= " title='" . $this->view->translate($element["hint"]) . "' ";
                $elementfield .= "value='" . $element["value"] . "' />\n\t\t</td>\n\t</tr>\n\t";
                $elementfield .= "<tr>\n\t\t<td colspan='2'>";
                if ($element["error"] != null) {
                    $elementfield .= "<ul class='errors'>";
                    foreach ($element["error"] AS $err)
                        $elementfield .= "\n\t\t\t<li>" . $err . "</li>";
                    $elementfield .= "\n\t\t</ul>";
                }
                break;

            case "Zend_Form_Element_Select":
                $elementfield .= "\n\t\t\t<select name='" . $element["id"] . "' id='" . $element["id"] . "'>\n\t\t\t\t";
                foreach ($element["options"] AS $key => $option) {
                    $elementfield .= "<option value='" . $key . "' label='" . $option . "'";
                    $elementfield .= " title='" . $this->view->translate($element["hint"]) . "' ";

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
                break;
        }
        $elementfield .= "</td>\n\t</tr>\n</table></fieldset>\n\n";
        return $elementfield;
    }

    /**
     * Method to render a group of elements (group fields, buttons, hidden fields)
     * @param <Array> $group
     */
    public function _renderGroup($name, $group) {
        $fieldset = "";
        if (isset($group)) {
            $fieldset = "<fieldset class='fieldset'>\n<legend class='legend'>" . $this->view->translate($group['Name']) . "</legend>\n\t";
            $fieldset .= "<table class='table' width='100%'>";
            $fieldset .= "<a name='". $group['Name'] ."' />";
            
            //show fields
            foreach ($group["Fields"] AS $field) {
                $fieldset .= "\n\t\t<tr>\n\t\t\t<td width='25%' align='right' valign='top'>";
                $fieldset .= "\n\t\t\t\t<label for='" . $field["id"] . "'>" . $field["label"] . "</label>";
                $fieldset .= "\n\t\t\t</td>\n\t\t\t<td valign='top'>";
                switch ($field['type']) {
                    case "Zend_Form_Element_Text":
                        $fieldset .= "\n\t\t\t\t<input type='text' name='" . $field["id"] . "' id='" . $field["id"] . "'";

                        if ($field["disabled"] === true)
                            $fieldset .= " disabled='1' ";

                        if (strstr($field["id"], "1"))
                            $fieldset .= " title='" . $this->view->translate($field["hint"]) . "' ";
                        $fieldset .= " value='" . $field["value"] . "' />";

                        if (isset ($field["desc"]))
                            $fieldset .= "<p class='description'>" . $this->view->translate($field["desc"]) . "</p>";

                        if ($field["req"] === 'required')
                            $fieldset .= $this->getRequiredSign();
                        break;

                    case "Zend_Form_Element_Textarea":
                        $fieldset .= "\n\t\t\t\t<textarea cols='30' rows='9' name='" . $field["id"] . "'";

                        if (strstr($field["id"], "1"))
                            $fieldset .= " title='" . $this->view->translate($field["hint"]) . "' ";
                        
                        $fieldset .=  " id='" . $field["id"] . "'>". $field["value"] . "</textarea>";

                        if ($field["req"] === 'required')
                            $fieldset .= $this->getRequiredSign();
                        break;

                    case "Zend_Form_Element_Select" :
                        $fieldset .= "\n\t\t\t\t<select name='" . $field["id"] . "' id='" . $field["id"] . "'";
                         if (strstr($field["id"], "1"))
                            $fieldset .= " title='" . $this->view->translate($field["hint"]) . "'";
                         $fieldset .= ">\n\t\t\t\t\t";
                         
                        foreach ($field["options"] AS $key => $option) {
                            $fieldset .= "<option value='" . $key . "' label='" . $option . "'";
                                                       
                            if ($option === $field["value"] || $key === $field["value"])
                                $fieldset .= " selected='selected'>";
                            else
                                $fieldset .= ">";
                            $fieldset .= $option . "</option>\n\t\t\t\t\t";
                        }
                        $fieldset .= "</select>";
                        if ($field["req"] === 'required')
                            $fieldset .= $this->getRequiredSign();
                        break;
                        
                    default:
                        throw new Application_Exception("Field Type {$field['type']} not found in View Helper.");
                }
                $fieldset .= "\n\t\t\t</td>\n\t\t\t";
                $fieldset .= "</tr>\n\t\t<tr>\n\t\t\t<td colspan='2'>";
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
                $fieldset .= "\n\t\t<tr>\n\t\t\t<td></td><td align='right'>";
                $fieldset .= "\n\t\t\t\t<input type='submit' name='" . $button["id"] . "' id='" . $button["id"] . "' value='" . $button["label"] . "' />";
                $fieldset .= "\n\t\t\t</td>\n\t\t</tr>";
            }
            //show hidden fields
            foreach ($group["Hiddens"] AS $hidden) {
                $fieldset .= "\n<input type='hidden' name='" . $hidden["id"] . "' id='" . $hidden["id"] . "' value='" . $hidden["value"] . "' />";
            }
            $fieldset .= "<tr><td colspan='2' valign='top'>" . $this->getGroupHint($group['Name']) . "</td></tr>";
            $fieldset .= "\n\t</table>\n</fieldset>\n\n";
        }
        return $fieldset;
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
     * @param <type> $value
     * @param <type> $name
     * @return string 
     */
    public function _renderHidden($name, $value) {
        $hiddenfield = "<input type='hidden' name='" . $name . "' id='" . $name . "' value='" . $value . "' />";
        return $hiddenfield;
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

    /**
     * returns the hint string for a required element
     * @return <type> 
     */
    protected function getRequiredSign() {
        return "<span class='required'>*</span>";
    }

    /**
     * returns a html String for displaying the group hint
     * @param <String> $groupName
     * @return <type>
     */
    protected function getGroupHint($groupName) {
        return "<table class='table'><tr><td><span class='hint'>?</span></td><td>" . $this->view->translate('hint_' . $groupName) . "</td></tr></table>";
    }

}

?>
