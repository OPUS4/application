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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Sorgt dafür, daß in den Unterformularen geprüft wird, ob eine Sprache bereits benutzt wurde.
 *
 * TODO Redundanz mit RepeatedInstitutes eliminieren
 */
class Application_Form_Validate_MultiSubForm_RepeatedLanguages implements Application_Form_Validate_MultiSubFormInterface
{
    /**
     * Es werden keine Validierungen auf Formularebene ausgeführt.
     *
     * @param array      $data
     * @param null|array $context
     * @return true Immer true
     */
    public function isValid($data, $context = null)
    {
        return true;
    }

    /**
     * Bereitet die Validierung vor.
     *
     * Zu jedem Language-Element in den Unterformularen wird ein zusätzlicher Validator hinzugefügt, der die POST Daten
     * für alle Unterformulare und die Position des Formulars mitbekommt, damit die Prüfung ausgeführt werden kann.
     *
     * @param Zend_Form  $form
     * @param array      $data
     * @param null|array $context
     */
    public function prepareValidation($form, $data, $context = null)
    {
        $position = 0;

        $languages = $this->getSelectedLanguages($data);

        foreach ($form->getSubForms() as $name => $subform) {
            if (array_key_exists($name, $data)) {
                $element = $subform->getElement(Admin_Form_Document_Title::ELEMENT_LANGUAGE);
                if ($element !== null) {
                    $element->addValidator(new Application_Form_Validate_LanguageUsedOnceOnly($languages, $position++));
                }
            }
        }
    }

    /**
     * Liefert die ausgewählten Sprachen für jedes Unterformular (alle Titel gleichen Typs).
     *
     * @param array $parentContext
     * @return array
     */
    public function getSelectedLanguages($parentContext)
    {
        $values = [];

        foreach ($parentContext as $index => $entry) {
            if (isset($entry[Admin_Form_Document_Title::ELEMENT_LANGUAGE])) {
                $values[] = $entry[Admin_Form_Document_Title::ELEMENT_LANGUAGE];
            }
        }

        return $values;
    }
}
