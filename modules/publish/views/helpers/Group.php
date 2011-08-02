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
class View_Helper_Group extends Zend_View_Helper_Abstract {

    public $session;

    /**
     * method to render specific elements of an form
     * @param <type> $type element type that has to rendered
     * @param <type> $value value of element or Zend_Form_Element
     * @param <type> $name name of possible hidden element
     * @return element to render in view
     */
    public function group($value, $options = null, $name = null) {
        $this->session = new Zend_Session_Namespace('Publish');        
        $this->view->count++;

        if ($name == null && $value == null) {
            $error_message = $this->view->translate('template_error_unknown_field');
            return "<br/><div style='width: 400px; color:red;'>" . $error_message . "</div><br/><br/>";
        }
        else {
            $result = $this->_renderGroup($value, $options, $name);
            return $result;
        }
    }

    /**
     * Method to render a group of elements (group fields, buttons, hidden fields)
     * @param <Array> $group
     */
    protected function _renderGroup($group, $options= null, $name = null) {
        $fieldset = "";
        $disable = false;
        if (isset($group)) {

            if (isset($this->session->invalidForm) && $this->session->invalidForm == '1')
                $fieldset .= "";
            else {
                if ($this->session->currentAnchor == $group['Name'])
                    $fieldset .= "<a name='current'></a>";
            }

            $fieldset .= "<fieldset class='left-labels' id='" . $group['Name'] . "' />\n";
            $fieldset .= "<legend>" . $this->view->translate($group['Name']) . "</legend>\n\t";
            $fieldset .= "<div class='description hint'><p>" . $this->_getGroupHint($group['Name']) . "</div></p>";

            //inital div class
            $fieldset .= "<div class='form-multiple odd'>";
            $i = 1;

            //show fields
            foreach ($group["Fields"] AS $field) {
                $groupCount = 'num' . $group['Name'];
                $j = (($i - 1) / $this->session->$groupCount);
                if (is_int($j)) {
                    //show other div in case intial field number is extended
                    if (fmod($j, 2) == 1)
                        $fieldset .= "</div><div class='form-multiple even'>";
                    else
                        $fieldset .= "</div><div class='form-multiple odd'>";
                }

                $fieldset .= "\n<div class='form-item'>\n";
                $fieldset .= "<label for='" . $field["id"] . "'>" . $field["label"];

                if ($field["req"] === 'required')
                    $fieldset .= $this->_getRequiredSign();
                $fieldset .= "</label>";

                switch ($field['type']) {

                    case "Zend_Form_Element_Text":
                        if (!is_null($this->session->endOfCollectionTree)) {
                            if (!array_key_exists($field['id'], $this->session->endOfCollectionTree)) {
                                $fieldset .= "\n\t\t\t\t<input type='text' class='form-textfield' name='" . $field['id'] . "' id='" . $field['id'] . "' ";
                                if ($options !== null)
                                    $fieldset .= $options . " ";
                                else
                                    $fieldset .= "size='30' ";

                                if ($field["disabled"] === true) {
                                    $fieldset .= " disabled='1' ";
                                }

                                if (strstr($field["id"], "1"))
                                    $fieldset .= " title='" . $this->view->translate($field["hint"]) . "' ";
                                $fieldset .= " value='" . htmlspecialchars($field["value"]) . "' />\n";

                                if (isset($field['desc']))
                                    $fieldset .= '<div class="description hint">' . $this->view->translate($field['desc']) . '</div>';
                            }
                        }
                        break;

                    case "Zend_Form_Element_Textarea":
                        $fieldset .= "\n\t\t\t\t<textarea name='" . $field["id"] . "' class='form-textarea' ";
                        if ($options !== null)
                            $fieldset .= $options . " ";
                        else
                            $fieldset .= "cols='30' rows='5' ";

                        if (strstr($field["id"], "1"))
                            $fieldset .= " title='" . $this->view->translate($field["hint"]) . "' ";

                        $fieldset .= " id='" . $field["id"] . "'>" . htmlspecialchars($field["value"]) . "</textarea>";

                        break;

                    case "Zend_Form_Element_Select" :
                        $fieldset .= "\n\t\t\t\t" . '<select style="width:300px" name="' . $field['id'] . '" class="form-selectfield"  id="' . $field['id'] . '"';
                        if (strstr($field['id'], '1'))
                            $fieldset .= ' title="' . $this->view->translate($field['hint']) . '"';
                        if ($field["disabled"] === true) {
                            $fieldset .= ' disabled="1" ';
                        }
                        $fieldset .= '>' . "\n\t\t\t\t\t";

                        foreach ($field['options'] AS $key => $option) {
                            $fieldset .= '<option value="' . htmlspecialchars($key) . '" label="' . htmlspecialchars($option) . '"';

                            if ($option === $field['value'] || $key === $field['value'])
                                $fieldset .= ' selected="selected"';

                            $fieldset .= '>';
                            $fieldset .= htmlspecialchars($option) . '</option>' . "\n\t\t\t\t\t";
                        }
                        $fieldset .= '</select>' . "\n";

                        if (isset($field['desc']))
                            $fieldset .= '<div class="description hint">' . $this->view->translate($field['desc']) . '</div>';

                        break;

                    case 'Zend_Form_Element_Checkbox' :
                        $fieldset .= "<input type='hidden' name='" . $field['id'] . "' value='0' />";
                        $fieldset .= "\n\t\t\t\t<input type='checkbox' class='form-checkbox' name='" . $field['id'] . "' id='" . $field['id'] . "' ";
                        $fieldset .= "value='" . $field['value'] . "' ";
                        if ($field['check'] == 'checked')
                            $fieldset .= " checked='checked' ";

                        if ($field["disabled"] === true) {
                            $fieldset .= " disabled='disabled' ";
                            $disable = true;
                        }

                        $fieldset .= " />\n";
                        break;

                    case 'Zend_Form_Element_File' :
                        $fieldset .= "<input type='file' name='" . $field['id'] . "' id='" . $field['id'] . "' enctype='multipart/form-data' ";
                        $fieldset .= "title='" . $this->view->translate($field['hint']) . "' ";
                        if ($options !== null)
                            $fieldset .= $options . " ";
                        else
                            $fieldset .= "size='30'";
                        $fieldset .= " />\n";

                        break;


                    default:
                        throw new Application_Exception("Field Type {$field['type']} not found in View Helper.");
                }

                if (isset($field["error"]) && count($field["error"]) >= 1) {                    
                    $fieldset .= "<div class='form-errors'><ul>";
                    foreach ($field["error"] AS $err)
                        $fieldset .= "<li>" . htmlspecialchars($err) . "</li>";
                    $fieldset .= "</ul></div>";
                }

                $fieldset .= "</div>";
                $i++;
            }

            //close inital div class
            $fieldset .= "</div>";

            //show buttons
            $fieldset .= "\n\t\t\t\t<div class='button-wrapper add-delete-wrapper'>";
            foreach ($group["Buttons"] AS $button) {
                if (!$disable) {
                    $fieldset .= "<input type='submit' ";
                    if (strstr($button['id'], 'Down') !== false)
                        $fieldset .= "class='form-button down-button' ";
                    else {
                        if (strstr($button['id'], 'Up') !== false)
                            $fieldset .= "class='form-button up-button' ";
                    }

                    if (strstr($button['id'], 'add') !== false)
                        $fieldset .= "class='form-button add-button' ";
                    else {
                        if (strstr($button['id'], 'delete') !== false)
                            $fieldset .= "class='form-button delete-button' ";
                    }

                    $fieldset .= "name='" . $button["id"] . "' id='" . $button["id"] . "' value='" . $button["label"] . "' />&nbsp;";
                }
            }
            $fieldset .= "</div>";

            //show hidden fields
            foreach ($group["Hiddens"] AS $hidden) {
                $fieldset .= "\n<input type='hidden' name='" . $hidden["id"] . "' id='" . $hidden["id"] . "' value='" . $hidden["value"] . "' />";
            }

            $fieldset .= "\n\n</fieldset>\n\n";
        }
        return $fieldset;
    }

    /**
     * returns the hint string for a required element
     * @return <type> 
     */
    protected function _getRequiredSign() {
        return "<span class='required' title='" . $this->view->translate('required_star_title') . "'>*</span>";
    }

    /**
     * returns a html String for displaying the group hint
     * @param <String> $groupName
     * @return <type>
     */
    protected function _getGroupHint($groupName) {
        return $this->view->translate('hint_' . $groupName);
    }

}

?>
