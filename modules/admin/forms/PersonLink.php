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

use Opus\Model\Dependent\Link\DocumentPerson;

/**
 * Formular für die Felder von DocumentPerson.
 *
 * Das sind folgende Felder.
 *
 * - PersonId
 * - Role
 * - AllowEmailContact
 * - SortOrder
 */
class Admin_Form_PersonLink extends Admin_Form_AbstractDocumentSubForm
{
    /**
     * Nachricht für Funktionsaufruf mit falschem Model Parameter.
     */
    public const BAD_MODEL_MESSAGE = ' Called with object that is not instance of Opus\Model\Dependent\Link\DocumentPerson';

    /**
     * Name fuer Formularelement fuer Feld AllowEmailContact.
     */
    public const ELEMENT_ALLOW_CONTACT = 'AllowContact';

    /**
     * Name fuer Formularelement fuer Feld Role.
     */
    public const ELEMENT_ROLE = 'Role';

    /**
     * Name fuer Formularelement fuer Feld SortOrder.
     */
    public const ELEMENT_SORT_ORDER = 'SortOrder';

    /**
     * Link-Model das angezeigt wird.
     *
     * @var DocumentPerson
     */
    private $model;

    /**
     * Erzeugt die Formularelemente.
     */
    public function init()
    {
        parent::init();

        $this->addElement(
            'hidden',
            Admin_Form_Person::ELEMENT_PERSON_ID,
            [
                'required'   => true,
                'validators' => ['Int'],
            ]
        );
        $this->addElement('PersonRole', self::ELEMENT_ROLE, ['label' => 'Role']);
        $this->addElement('checkbox', self::ELEMENT_ALLOW_CONTACT, ['label' => 'AllowEmailContact']);
        $this->addElement('SortOrder', self::ELEMENT_SORT_ORDER, ['label' => 'SortOrder']);
    }

    /**
     * Initialisiert Formular mit Werten aus Model.
     *
     * @param DocumentPerson $personLink
     */
    public function populateFromModel($personLink)
    {
        if ($personLink instanceof DocumentPerson) {
            $this->getElement(self::ELEMENT_ALLOW_CONTACT)->setValue($personLink->getAllowEmailContact());
            $this->getElement(self::ELEMENT_SORT_ORDER)->setValue($personLink->getSortOrder());
            $this->getElement(Admin_Form_Person::ELEMENT_PERSON_ID)->setValue($personLink->getModel()->getId());
            $this->getElement(self::ELEMENT_ROLE)->setValue($personLink->getRole());
            $this->model = $personLink;
        } else {
            $this->getLogger()->err(__METHOD__ . self::BAD_MODEL_MESSAGE);
        }
    }

    /**
     * Setzt Werte im Model mit dem Inhalt der Formularelemente.
     *
     * @param DocumentPerson $personLink
     */
    public function updateModel($personLink)
    {
        if ($personLink instanceof DocumentPerson) {
            $personLink->setAllowEmailContact($this->getElementValue(self::ELEMENT_ALLOW_CONTACT));
            $personLink->setSortOrder($this->getElementValue(self::ELEMENT_SORT_ORDER));
            $personLink->setRole($this->getElementValue(self::ELEMENT_ROLE));
        } else {
            $this->getLogger()->err(__METHOD__ . self::BAD_MODEL_MESSAGE);
        }
    }

    /**
     * Liefert angezeigtes Model.
     *
     * @return DocumentPerson
     */
    public function getModel()
    {
        return $this->model;
    }
}
