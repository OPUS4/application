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
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
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
 * TODO use constants for element names
 */
class Admin_Form_DocumentPatent extends Admin_Form_AbstractModelSubForm {
    
    const ELEMENT_ID = 'Id';
    
    const ELEMENT_NUMBER = 'Number';
    
    const ELEMENT_COUNTRIES = 'Countries';
    
    const ELEMENT_YEAR_APPLIED = 'YearApplied';
    
    const ELEMENT_APPLICATION = 'Application';
    
    const ELEMENT_DATE_GRANTED = 'DateGranted';

    protected $_translationPrefix = ''; // TODO OPUSVIER-1875 Sollte sein: 'Opus_Patent_';
    
    public function init() {
        parent::init();
        
        $elementFactory = new Admin_Model_FormElementFactory();
        
        $element = new Zend_Form_Element_Hidden(self::ELEMENT_ID);
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Text(self::ELEMENT_NUMBER);
        $element->setLabel($this->_translationPrefix . self::ELEMENT_NUMBER);
        $element->setRequired(true);
        $element->setValidators(array(
           new Zend_Validate_NotEmpty() 
        ));
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Text(self::ELEMENT_COUNTRIES);
        $element->setLabel($this->_translationPrefix . self::ELEMENT_COUNTRIES);
        $this->addElement($element);
        
        $element = $elementFactory->createYearElement(self::ELEMENT_YEAR_APPLIED);
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Text(self::ELEMENT_APPLICATION);
        $element->setLabel($this->_translationPrefix . self::ELEMENT_APPLICATION);
        $this->addElement($element);
        
        $element = $elementFactory->createDateElement(self::ELEMENT_DATE_GRANTED);
        $this->addElement($element);
    }
    
    public function populateFromModel($patent) {
        $datesHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('Dates');
        
        // TODO check class of $patent
        $this->getElement(self::ELEMENT_ID)->setValue($patent->getId());
        $this->getElement(self::ELEMENT_NUMBER)->setValue($patent->getNumber());
        $this->getElement(self::ELEMENT_COUNTRIES)->setValue($patent->getCountries());
        $this->getElement(self::ELEMENT_YEAR_APPLIED)->setValue($patent->getYearApplied());
        $this->getElement(self::ELEMENT_APPLICATION)->setValue($patent->getApplication());
        
        $date = $datesHelper->getDateString($patent->getDateGranted());
        $this->getElement(self::ELEMENT_DATE_GRANTED)->setValue($date);
    }
    
    /**
     * 
     * @param Opus_Patent $patent
     * 
     * TODO Prüfe ID match?
     */
    public function updateModel($patent) {
        $datesHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('Dates');
        
        // Number
        $value = $this->getElement(self::ELEMENT_NUMBER)->getValue();
        $patent->setNumber($value); 
        
        // Countries
        $value = $this->getElement(self::ELEMENT_COUNTRIES)->getValue();
        $patent->setCountries($value);
        
        // YearApplied
        $value = $this->getElement(self::ELEMENT_YEAR_APPLIED)->getValue();
        $patent->setYearApplied($value);
        
        // Application
        $value = $this->getElement(self::ELEMENT_APPLICATION)->getValue();
        $patent->setApplication($value);
        
        // DateGranted
        $value = $this->getElement(self::ELEMENT_DATE_GRANTED)->getValue();
        $date = $datesHelper->getOpusDate($value);
        $patent->setDateGranted($date);
    }
    
    public function getModel() {
        $patentId = $this->getElement(self::ELEMENT_ID)->getValue();
        
        // TODO empty not suffiecient (e.g. '00' is empty)
        if (empty($patentId)) {
            $patentId = null;
        }
        
        $patent = new Opus_Patent($patentId);
        
        $this->updateModel($patent);
        
        return $patent;
    }

}
