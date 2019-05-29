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
 * @package     Module_Admin
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Anzeigefeld für den Registrierungsstatus von lokalen DOIs
 */
class Admin_Form_Document_RegistrationNote extends Admin_Form_AbstractDocumentSubForm {

    /**
     * Name für Anzeigefeld, in dem der Registrierungsstatus von DOIs angezeigt
     */
    const ELEMENT_REGISTRATION_NOTE = 'RegistrationNote';

    private $identifier;

    public function getIdentifier() {
        return $this->identifier;
    }

    public function init() {
        parent::init();

        $statusNote = new Zend_Form_Element_Note(self::ELEMENT_REGISTRATION_NOTE);
        $this->addElement($statusNote);
    }

    public function populateFromModel($model) {
        if (is_string($model) && is_numeric($model)) {
            // Identifier-ID übergeben
            try {
                $model = new Opus_Identifier($model);
            }
            catch (Opus_Model_NotFoundException $e) {
                // ignore silently
                return;
            }
        }

        if (!($model instanceof Opus_Identifier)) {
            return;
        }
        if (!$model->isLocalDoi()) {
            return; // Statusinformationen werden nur für lokale DOIs (gemäß der Konfiguration) angezeigt
        }

        $this->identifier = $model;
    }

    public function loadDefaultDecorators() {
        $this->setDecorators(
            array(
                array(
                    'ViewScript',
                    array('viewScript' => 'identifierStatus.phtml')
                )
            )
        );
    }

    /**
     * Diese Methode wird aufgerufen, wenn das Formular im Nicht-Edit-Modus angezeigt werden soll.
     */
    public function prepareRenderingAsView() {
        if (is_null($this->identifier)) {
            return; // es wird keine Information angezeigt
        }

        $this->setViewModeEnabled();

        // in diesem Fall soll der DOI-Registrierungsstatus angezeigt werden (statt einer Warnung, wenn DOI bereits registiert ist)
        $element = $this->getElement(self::ELEMENT_REGISTRATION_NOTE);
        $element->setValue('Diese DOI ist bereits registriert. Sie sollte nicht mehr geändert werden!');

    }

}