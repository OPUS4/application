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
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Publish_View_Helper_Group extends Publish_View_Helper_Fieldset {
   
    /**
     * method to render specific elements of an form
     * @param <type> $type element type that has to rendered
     * @param <type> $value value of element or Zend_Form_Element
     * @param <type> $name name of possible hidden element
     * @return element to render in view
     */
    public function group($value, $options = null, $name = null) {        
        $this->view->count++;

        if ($name == null && $value == null) {
            $error_message = $this->view->translate('template_error_unknown_field');
            return "<br/><div style='width: 400px; color:red;'>" . $error_message . "</div><br/><br/>";
        } else {
            $result = $this->_renderGroup($value, $options, $name);
            return $result;
        }
    }

    /**
     * Method to render a group of elements (group fields, buttons, hidden fields)
     * @param <Array> $group
     */
    function _renderGroup($group, $options= null, $name = null) {
        $fieldset = "";

        if (!isset($group))
            return $fieldset;

        if ($this->view->currentAnchor == $group['Name'])
            $fieldset .= "<a name='current'></a>";

        $fieldset .= "<fieldset class='left-labels' id='" . $group['Name'] . "' />\n";
        $fieldset .= $this->getLegendFor($group['Name']);
        $fieldset .= $this->getFieldsetHint($group['Name']);

        //inital div class
        $fieldset .= "<div class='form-multiple odd'>";
        $i = 1;

        //show fields
        foreach ($group["Fields"] AS $field) {
            $j = (($i - 1) / $group['Counter']);
            if (fmod($j, 2) == 1)
                $fieldset .= "</div><div class='form-multiple even'>";
            else
                $fieldset .= "</div><div class='form-multiple odd'>";

            $fieldset .= "\n<div class='form-item'>\n";
            $fieldset .= $this->getLabelFor($field["id"], $field["label"], $field['req']);

            switch ($field['type']) {

                case "Zend_Form_Element_Text":
                    $fieldset .= $this->renderHtmlText($field, $options);
                    break;

                case "Zend_Form_Element_Textarea":
                    $fieldset .= $this->renderHtmlTextarea($field, $options);
                    break;

                case "Zend_Form_Element_Select" :
                    $fieldset .= $this->renderHtmlSelect($field, $options);
                    break;

                case 'Zend_Form_Element_Checkbox' :
                    $fieldset .= $this->renderHtmlCheckbox($field, $options);
                    break;

                case 'Zend_Form_Element_File' :
                    $fieldset .= $this->renderHtmlFile($field, $options);
                    break;

                default:
                    break;
            }

            $fieldset .= $this->renderFieldsetErrors($field['error']);
            $fieldset .= "</div>";
            $i++;
        }

        //close inital div class
        $fieldset .= "</div>";

        //show buttons
        $fieldset .= $this->renderHtmlButtons($group['Buttons']);

        //show hidden fields
        $fieldset .= $this->renderHtmlHidden($group['Hiddens']);

        $fieldset .= "\n\n</fieldset>\n\n";

        return $fieldset;
    }

}

?>
