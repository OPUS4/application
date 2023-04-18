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
 * Formular für das Hinzufügen einer Person zu einem Dokument.
 */
class Admin_Form_Document_PersonAdd extends Admin_Form_Person
{
    /**
     * Name für Button, um weitere Person einzugeben.
     */
    public const ELEMENT_NEXT = 'Next';

    /**
     * Name für Unterformlar mit Dokument-Link Feldern.
     */
    public const SUBFORM_DOCUMENT = 'Document';

    /**
     * Konstante für Ergebnis nach Klicken auf 'Next' Button.
     */
    public const RESULT_NEXT = 'next';

    /**
     * Erzeugt die Formularelemente.
     *
     * Die Informationen für den Link mit dem Dokument werden als Unterformular hinzugefügt.
     */
    public function init()
    {
        parent::init();

        $linkForm = new Admin_Form_PersonLink();
        $linkForm->setLegend('admin_person_assign_document_options');

        // Für neue Personen kann das Link-Formular noch keine ID haben
        $linkForm->getElement(Admin_Form_Person::ELEMENT_PERSON_ID)->setRequired(false);

        $this->addSubForm($linkForm, self::SUBFORM_DOCUMENT, 20);

        $next = $this->createElement(
            'submit',
            self::ELEMENT_NEXT,
            [
                'decorators' => [
                    'ViewHelper',
                    [['liWrapper' => 'HtmlTag'], ['tag' => 'li', 'class' => 'save-element']],
                ],
            ]
        );

        $this->getDisplayGroup('actions')->setElements(
            [
                $this->getElement(self::ELEMENT_SAVE),
                $next,
                $this->getElement(self::ELEMENT_CANCEL),
            ]
        );
    }

    /**
     * @param array $post
     * @param array $context
     * @return string|null
     */
    public function processPost($post, $context)
    {
        $result = parent::processPost($post, $context);

        if ($result === null) {
            if (array_key_exists(self::ELEMENT_NEXT, $post)) {
                $result = self::RESULT_NEXT;
            }
        }

        return $result;
    }

    /**
     * @return string|null
     */
    public function getSelectedRole()
    {
        return $this->getSubForm(self::SUBFORM_DOCUMENT)->getElementValue(Admin_Form_PersonLink::ELEMENT_ROLE);
    }

    /**
     * Setzt die ausgewählte Rolle.
     *
     * Wenn eine unbekannte Rolle übergeben wird, wird die 'author' Rolle verwendet.
     *
     * @param string $role Rolle der Person für Dokument
     */
    public function setSelectedRole($role)
    {
        if (! in_array($role, Admin_Form_Document_Persons::getRoles())) {
            $this->getLogger()->err(__METHOD__ . " Called with unknown role '$role'.");
            $role = 'author';
        }

        $this->getSubForm(self::SUBFORM_DOCUMENT)->getElement(Admin_Form_PersonLink::ELEMENT_ROLE)->setValue($role);
    }

    /**
     * @param array $personId
     * @return array
     */
    public function getPersonLinkProperties($personId)
    {
        $linkForm = $this->getSubForm(self::SUBFORM_DOCUMENT);

        return [
            'person'  => $personId,
            'role'    => $linkForm->getElementValue(Admin_Form_PersonLink::ELEMENT_ROLE),
            'contact' => $linkForm->getElementValue(Admin_Form_PersonLink::ELEMENT_ALLOW_CONTACT),
            'order'   => $linkForm->getElementValue(Admin_Form_PersonLink::ELEMENT_SORT_ORDER),
        ];
    }
}
