<?php
/*
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
 * @package     Module_Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Unterformular fuer eine einem Dokument zugewiesene Person im Metadaten-Formular.
 */
class Admin_Form_Document_Person extends Admin_Form_PersonLink
{

    /**
     * Name fuer Button zum Editieren der Person.
     */
    const ELEMENT_EDIT = 'Edit';

    /**
     * Erzeugt die Formularelemente.
     */
    public function init()
    {
        parent::init();

        // Element 'Role' ist im Metadaten-Formular nicht required; Role wird durch
        // übergeordnetes Unterformular (für die Rolle) bestimmt
        $this->getElement(self::ELEMENT_ROLE)->setRequired(false);

        $this->addElement('submit', self::ELEMENT_EDIT, ['label' => 'admin_button_edit']);

        $this->setDecorators(
            [
            'PrepareElements',
            ['ViewScript', ['viewScript' => 'form/personForm.phtml']]
            ]
        );
    }

    /**
     * Verarbeitet POST Daten für Formular.
     * @param array $post
     * @param array $context
     * @return string
     */
    public function processPost($post, $context)
    {
        if (array_key_exists(self::ELEMENT_EDIT, $post)) {
            return [ 'result' => Admin_Form_Document::RESULT_SWITCH_TO,
                'target' => [
                'module' => 'admin',
                'controller' => 'person',
                'action' => 'editlinked',
                'personId' => $this->getElement(Admin_Form_Person::ELEMENT_PERSON_ID)->getValue()
                ]
            ];
        }

        return parent::processPost($post, $context);
    }

    /**
     * Liefert angezeigtes Model.
     *
     * Die ID für ein Opus_Model_Dependent_Link_DocumentPerson Objekt setzt sich aus Dokument-ID, Person-ID und Rolle
     * zusammen.
     *
     * @param int $documentId Identifier für das Dokument
     * @return \Opus_Model_Dependent_Link_DocumentPerson
     *
     * TODO rename in getModel() !!!Konflikt mit getModel in PersonLink auflösen
     * TODO personId darf nicht null sein
     */
    public function getLinkModel($documentId, $role)
    {
        $personId = $this->getElementValue(Admin_Form_Person::ELEMENT_PERSON_ID);

        try {
            $personLink = new Opus_Model_Dependent_Link_DocumentPerson([$personId, $documentId, $role]);
        } catch (Opus_Model_NotFoundException $opnfe) {
            $personLink = new Opus_Model_Dependent_Link_DocumentPerson();
            $person = new Opus_Person($personId);
            $personLink->setModel($person);
        }

        $this->updateModel($personLink);
        $personLink->setRole($role);

        if (Zend_Registry::get('LOG_LEVEL') >= Zend_Log::DEBUG) {
            $log = $this->getLogger();
            $log->debug(Zend_Debug::dump($personLink->getId(), 'DocumentPerson-ID', false));
            $log->debug(Zend_Debug::dump($personLink->getRole(), 'DocumentPerson-Role', false));
            $log->debug(Zend_Debug::dump($personLink->getSortOrder(), 'DocumentPerson-SortOrder', false));
            $log->debug(
                Zend_Debug::dump(
                    $personLink->getAllowEmailContact(),
                    'DocumentPerson->AllowEmailContact',
                    false
                )
            );
            $log->debug(Zend_Debug::dump($personLink->getModel()->getId(), 'DocumentPerson-Model-ID', false));
        }

        return $personLink;
    }

    /**
     * Bereitet Formular für Anzeige in Metadaten-Übersicht vor.
     *
     * Die Elemente 'SortOrder' und 'Role' sollen nicht angezeigt werden, auch wenn sie einen Wert haben. Leere
     * Elemente werden durch die Implementation in der Basisklasse automatisch entfernt.
     */
    public function prepareRenderingAsView()
    {
        parent::prepareRenderingAsView();

        $this->removeElement(self::ELEMENT_SORT_ORDER);
        $this->removeElement(self::ELEMENT_ROLE);
    }

    /**
     * Überschreibt setOrder damit das Feld 'SortOrder' auf den gleichen Wert + 1 gesetzt wird.
     *
     * Bei jedem POST werden die Personen in die richtige Reihenfolge gebracht, dadurch entspriche der Wert von
     * 'SortOrder' dem Wert von Order (0..n) der Unterformulare erhöht um Eins.
     *
     * @param int $order
     */
    public function setOrder($order)
    {
        parent::setOrder($order);
        $this->getElement(self::ELEMENT_SORT_ORDER)->setValue($order + 1);
    }
}
