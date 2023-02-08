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
class Publish_View_Helper_Element extends Publish_View_Helper_Fieldset
{
    /**
     * method to render specific elements of a form
     *
     * @param array       $value value of element or Zend_Form_Element
     * @param string|null $options TODO
     * @param string|null $type element type that has to rendered
     * @param string|null $name name of possible hidden element
     * @return string Element to render in view
     */
    public function element($value, $options = null, $type = null, $name = null)
    {
        $this->view->count++;
        if ($name === null && $value === null) {
            $errorMessage = $this->view->translate('template_error_unknown_field');
            // TODO move to CSS
            return "<br/><div style='width: 400px; color:red;'>" . $errorMessage . "</div><br/><br/>";
        }
        $method = "render" . $type;
        if (method_exists($this, $method) === true) {
            return $this->$method($value, $options, $name);
        }
        return $this->renderElement($value, $options, $name);
    }

    /**
     * @param array       $field
     * @param string|null $options
     * @param string|null $name
     * @return string
     */
    protected function renderElement($field, $options = null, $name = null)
    {
        $fieldset  = "<fieldset class='left-labels'>";
        $fieldset .= $this->getLegendFor($field['header']);
        $fieldset .= $this->getFieldsetHint($field['id']);
        $fieldset .= "<div class='form-item'>";
        $fieldset .= $this->getLabelFor($field['id'], $field['label'], $field['req']);

        switch ($field['type']) {
            case "Zend_Form_Element_Textarea":
                $fieldset .= $this->renderHtmlTextarea($field, $options);
                break;

            case "Zend_Form_Element_Text":
                $fieldset .= $this->renderHtmlText($field, $options);
                break;

            case "Zend_Form_Element_Select":
                $fieldset .= $this->renderHtmlSelect($field, $options);
                break;

            case 'Zend_Form_Element_Checkbox':
                $fieldset .= $this->renderHtmlCheckbox($field, $options);
                break;

            case "Zend_Form_Element_File":
                $fieldset .= $this->renderHtmlFile($field, $options);
                break;

            default:
                break;
        }

        $fieldset .= $this->renderFieldsetErrors($field['error']);
        $fieldset .= "</div></fieldset>\n\n";

        return $fieldset;
    }
}
