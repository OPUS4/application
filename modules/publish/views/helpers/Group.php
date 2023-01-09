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

class Publish_View_Helper_Group extends Publish_View_Helper_Fieldset
{
    /**
     * method to render specific elements of an form
     *
     * @param string      $value value of element or Zend_Form_Element
     * @param string|null $options
     * @param string|null $name name of possible hidden element
     * @return string Element to render in view
     */
    public function group($value, $options = null, $name = null)
    {
        $this->view->count++;
        if ($name === null && $value === null) {
            $errorMessage = $this->view->translate('template_error_unknown_field');
            // TODO move to CSS
            return "<br/><div style='width: 400px; color:red;'>$errorMessage</div><br/><br/>";
        }
        return $this->renderGroup($value, $options, $name);
    }

    /**
     * Method to render a group of elements (group fields, buttons, hidden fields)
     *
     * @param array       $group
     * @param array|null  $options
     * @param string|null $name
     * @return string
     */
    private function renderGroup($group, $options = null, $name = null)
    {
        $fieldset = "";

        if (! isset($group)) {
            return $fieldset;
        }

        if ($this->view->currentAnchor === $group['Name']) {
            $fieldset .= "<a name='current'></a>";
        }

        $fieldset .= "<fieldset class='left-labels' id='" . $group['Name'] . "'>";
        $fieldset .= $this->getLegendFor($group['Name']);
        $fieldset .= $this->getFieldsetHint($group['Name']);

        $groupCount        = 1;
        $groupElementCount = 0;
        $index             = 0;

        foreach ($group['Fields'] as $field) {
            // besonderer Mechanismus erforderlich für Collection Roles (CRs sind erkennbar, weil nur bei ihnen
            // $group['Counter'] auf null gesetzt wurde)
            // dort kann jede Gruppe aus unterschiedlich vielen Select-Boxen aufgebaut sein
            // daher greift der Mechanismus der Auswertung von $group['Counter'] hier nicht
            if (
                $group['Counter'] === null && $index > 0 && $field['label'] !== 'choose_collection_subcollection'
                    && $field['label'] !== 'endOfCollectionTree'
            ) {
                $groupCount++;
                $groupElementCount = 0;
                $fieldset         .= "</div>";
            }

            if ($groupElementCount === 0) {
                if ($groupCount % 2 === 0) {
                    $fieldset .= "<div class='form-multiple even'>";
                } else {
                    $fieldset .= "<div class='form-multiple odd'>";
                }
            }
            $groupElementCount++;

            $fieldset .= "<div class='form-item'>";
            $fieldset .= $this->getLabelFor($field["id"], $field["label"], $field['req']);

            switch ($field['type']) {
                case "Zend_Form_Element_Text":
                    $fieldset .= $this->renderHtmlText($field, $options);
                    break;

                case "Zend_Form_Element_Textarea":
                    $fieldset .= $this->renderHtmlTextarea($field, $options);
                    break;

                case "Zend_Form_Element_Select":
                    if (is_array($options)) {
                        $selectOptions = $options;
                    } else {
                        $selectOptions = null;
                    }
                    $fieldset .= $this->renderHtmlSelect($field, $selectOptions);
                    break;

                case 'Zend_Form_Element_Checkbox':
                    $fieldset .= $this->renderHtmlCheckbox($field, $options);
                    break;

                case 'Zend_Form_Element_File':
                    $fieldset .= $this->renderHtmlFile($field, $options);
                    break;

                default:
                    break;
            }

            $fieldset .= $this->renderFieldsetErrors($field['error']);
            $fieldset .= "</div>"; // div.form-item schließen

            // Mechanimus für alle Gruppenfelder, die keine Collection Roles sind
            if ($group['Counter'] !== null && $groupElementCount === intval($group['Counter'])) {
                $groupCount++;
                $groupElementCount = 0;
                $fieldset         .= "</div>"; // div.form-multiple schließen
            }
            $index++;
        }

        // besonderer Mechanismus für Collection Roles (s.o.)
        if ($group['Counter'] === null) {
            $fieldset .= "</div>";
        }

        //show buttons
        $fieldset .= $this->renderHtmlButtons($group['Buttons']);

        //show hidden fields
        $fieldset .= $this->renderHtmlHidden($group['Hiddens']);

        $fieldset .= "</fieldset>";

        return $fieldset;
    }
}
