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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Unterformular fuer Enrichments im Metadaten-Formular.
 */
class Admin_Form_Document_Enrichment extends Admin_Form_AbstractModelSubForm {
    
    /**
     * Name von Formularelement für Enrichment-ID.
     */
    const ELEMENT_ID = 'Id';
    
    /**
     * Name von Formularelement für Auswahl von EnrichmentKey.
     */
    const ELEMENT_KEY_NAME = 'KeyName';
    
    /**
     * Name von Formularelement für Enrichment Wert.
     */
    const ELEMENT_VALUE = 'Value';
    
    /**
     * Erzeugt die Formularelemente.
     */
    public function init() {
        parent::init();
        
        $this->addElement('Hidden', self::ELEMENT_ID);
        $this->addElement('EnrichmentKey', self::ELEMENT_KEY_NAME, array('required' => true));
        $this->addElement('Text', self::ELEMENT_VALUE, array('required' => true, 'size' => 60));
    }
    
    /**
     * Initialisiert Formular mit den Werten in Enrichment Modell.
     * @param Opus_Enrichment $enrichment
     */
    public function populateFromModel($enrichment) {
        $this->getElement(self::ELEMENT_ID)->setValue($enrichment->getId());
        $this->getElement(self::ELEMENT_KEY_NAME)->setValue($enrichment->getKeyName());
        $this->getElement(self::ELEMENT_VALUE)->setValue($enrichment->getValue());
    }
    
    /**
     * Aktualisiert Enrichment Modell mit Werten im Formular.
     * @param Opus_Enrichment $enrichment
     */
    public function updateModel($enrichment) {
        $enrichment->setKeyName($this->getElementValue(self::ELEMENT_KEY_NAME));
        $enrichment->setValue($this->getElementValue(self::ELEMENT_VALUE));
    }

    /**
     * Liefert angezeigtes oder neues (hinzuzufügendes) Enrichment Modell.
     * @return \Opus_Enrichment
     */
    public function getModel() {
        $enrichmentId = $this->getElement(self::ELEMENT_ID)->getValue();
        
        if (empty($enrichmentId) && !is_numeric($enrichmentId)) {
            $enrichmentId = null;
        }

        try {
            $enrichment = new Opus_Enrichment($enrichmentId);
        }
        catch (Opus_Model_NotFoundException $omnfe) {
            $this->getLogger()->err(__METHOD__ . " Unknown enrichment ID = '$enrichmentId' (" . $omnfe->getMessage()
                . ').');
            $enrichment = new Opus_Enrichment();
        }
        
        $this->updateModel($enrichment);
        
        return $enrichment;
    }
        
    /**
     * Lädt die Dekoratoren für dieses Formular.
     */
    public function loadDefaultDecorators() {
        parent::loadDefaultDecorators();
        
        $this->removeDecorator('Fieldset');
    }

}
