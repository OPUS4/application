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
 */

/**
 * Formular für Opus_Patent Objekte.
 * 
 * Felder:
 * - Countries
 * - DateGranted
 * - Number (required, not empty)
 * - YearApplied
 * - Application
 * - ID (hidden)
 *
 * @category    Application
 * @package     Module_Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Admin_Form_Document_Patent extends Admin_Form_AbstractModelSubForm {
    
    /**
     * Name fuer Formularelement fuer ID von Opus_Patent.
     */
    const ELEMENT_ID = 'Id';
    
    /**
     * Name fuer Formularelement fuer Feld Number.
     */
    const ELEMENT_NUMBER = 'Number';
    
    /**
     * Name fuer Formularelement fuer Feld Countries.
     */
    const ELEMENT_COUNTRIES = 'Countries';
    
    /**
     * Name fuer Formularelement fuer Feld YearApplied.
     */
    const ELEMENT_YEAR_APPLIED = 'YearApplied';
    
    /**
     * Name fuer Formularelement fuer Feld Application.
     */
    const ELEMENT_APPLICATION = 'Application';
    
    /**
     * Name fuer Formularelement fuer Feld DateGranted.
     */
    const ELEMENT_DATE_GRANTED = 'DateGranted';

    /**
     * Präfix fuer Übersetzungsschlüssel (noch nicht verwendet).
     * @var string
     */
    protected $_translationPrefix = ''; // TODO OPUSVIER-1875 Sollte sein: 'Opus_Patent_';
    
    /**
     * Erzeugt die Formularelemente.
     */
    public function init() {
        parent::init();
        
        $this->addElement('hidden', self::ELEMENT_ID);
        $this->addElement('text', self::ELEMENT_NUMBER, array('required' => true, 'label' => 'Number'));
        $this->addElement('text', self::ELEMENT_COUNTRIES, array('required' => true, 'label' => 'Countries'));
        $this->addElement('Year', self::ELEMENT_YEAR_APPLIED);
        $this->addElement('text', self::ELEMENT_APPLICATION,
            array('required' => true, 'label' => 'Application', 'size' => 60)
        );
        $this->addElement('Date', self::ELEMENT_DATE_GRANTED);
    }

    /**
     * Setzt die Formularelement entsprechend der Instanz von Opus_Patent.
     * @param Opus_Patent $patent
     */
    public function populateFromModel($patent) {
        $datesHelper = $this->getDatesHelper();
        
        $this->getElement(self::ELEMENT_ID)->setValue($patent->getId());
        $this->getElement(self::ELEMENT_NUMBER)->setValue($patent->getNumber());
        $this->getElement(self::ELEMENT_COUNTRIES)->setValue($patent->getCountries());
        $this->getElement(self::ELEMENT_YEAR_APPLIED)->setValue($patent->getYearApplied());
        $this->getElement(self::ELEMENT_APPLICATION)->setValue($patent->getApplication());
        
        $date = $datesHelper->getDateString($patent->getDateGranted());
        $this->getElement(self::ELEMENT_DATE_GRANTED)->setValue($date);
    }
    
    /**
     * Aktualisiert Instanz von Opus_Patent mit Werten in Formular.
     * @param Opus_Patent $patent
     */
    public function updateModel($patent) {
        $datesHelper = $this->getDatesHelper();
        
        $patent->setNumber($this->getElementValue(self::ELEMENT_NUMBER)); 
        $patent->setCountries($this->getElementValue(self::ELEMENT_COUNTRIES));
        $patent->setYearApplied($this->getElementValue(self::ELEMENT_YEAR_APPLIED));
        $patent->setApplication($this->getElementValue(self::ELEMENT_APPLICATION));
        
        $value = $this->getElement(self::ELEMENT_DATE_GRANTED)->getValue();
        $date = $datesHelper->getOpusDate($value);
        $patent->setDateGranted($date);
    }
    
    /**
     * Liefert Opus_Patent Instanz fuer das Formular.
     * 
     * Wenn das Formular eine existierende Opus_Patent Instanz repräsentiert (gesetztes ID Feld) wird diese Instanz
     * zurück geliefert und ansonsten eine neue Instanz erzeugt.
     * 
     * @return \Opus_Patent
     */
    public function getModel() {
        $patentId = $this->getElement(self::ELEMENT_ID)->getValue();
        
        if (strlen(trim($patentId)) == 0 || !is_numeric($patentId)) {
            $patentId = null;
        }
        
        try {
            $patent = new Opus_Patent($patentId);
        }
        catch (Opus_Model_NotFoundException $omnfe) {
            // kann eigentlich nur bei manipuliertem POST passieren
            $this->getLog()->err($omnfe);
            // bei ungültiger ID wird Patentwie neu hinzugefügt behandelt
            $patent = new Opus_Patent(); 
        }
        
        $this->updateModel($patent);
        
        return $patent;
    }
        
    /**
     * Überschreibt Funktion fuer das Laden der Default-dekorators.
     * 
     * Der Fieldset Dekorator wird entfernt, damit nicht um jedes Patent ein weiteres Fieldset erzeugt wird.
     */
    public function loadDefaultDecorators() {
        parent::loadDefaultDecorators();
        
        $this->removeDecorator('Fieldset');
    }

}
