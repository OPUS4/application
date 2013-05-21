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
 * Formular fuer allgemeine Felder von Opus_Document.
 * 
 * TODO validierung
 */
class Admin_Form_DocumentGeneral extends Admin_Form_AbstractDocumentSubForm {
    
    const ELEMENT_LANGUAGE = 'Language';
    
    const ELEMENT_TYPE = 'Type';
    
    const ELEMENT_PUBLISHED_DATE = 'PublishedDate';
    
    const ELEMENT_PUBLISHED_YEAR = 'PublishedYear';
    
    const ELEMENT_COMPLETED_DATE = 'CompletedDate';
    
    const ELEMENT_COMPLETED_YEAR = 'CompletedYear';
    
    protected $_translationPrefix = '';
    
    public function init() {
        parent::init();
        
        $elementFactory = new Admin_Model_FormElementFactory(); // TODO make controller helper
        
        // TODO Sprache
        $element = $elementFactory->createLanguageSelect(self::ELEMENT_LANGUAGE);
        $element->setLabel($this->_translationPrefix . self::ELEMENT_LANGUAGE);
        $this->addElement($element);
        
        // TODO DocumentType
        $element = $elementFactory->createDocumentTypeSelect(self::ELEMENT_TYPE);
        $element->setLabel($this->_translationPrefix . self::ELEMENT_TYPE);
        $this->addElement($element);
        
        // PublishedDate
        $element = $elementFactory->createDateElement(self::ELEMENT_PUBLISHED_DATE);
        $this->addElement($element);
        
        // PublishedYear
        $element = $elementFactory->createYearElement(self::ELEMENT_PUBLISHED_YEAR);
        $this->addElement($element);
        
        // CompletedDate
        $element = $elementFactory->createDateElement(self::ELEMENT_COMPLETED_DATE);
        $this->addElement($element);
        
        // CompletedYear
        $element = $elementFactory->createYearElement(self::ELEMENT_COMPLETED_YEAR);
        $this->addElement($element);
    }
    
    public function populateFromModel($document) {
        $datesHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('Dates');
        
        $this->getElement(self::ELEMENT_LANGUAGE)->setValue($document->getLanguage());
        $this->getElement(self::ELEMENT_TYPE)->setValue($document->getType());

        $date = $datesHelper->getDateString($document->getCompletedDate());
        $this->getElement(self::ELEMENT_COMPLETED_DATE)->setValue($date);
        $this->getElement(self::ELEMENT_COMPLETED_YEAR)->setValue($document->getCompletedYear());
        
        $date = $datesHelper->getDateString($document->getPublishedDate());
        $this->getElement(self::ELEMENT_PUBLISHED_DATE)->setValue($date);
        $this->getElement(self::ELEMENT_PUBLISHED_YEAR)->setValue($document->getPublishedYear());
    }
        
    public function updateModel($document) {
        // Language
        $value = $this->getElement(self::ELEMENT_LANGUAGE)->getValue();
        $document->setLanguage($value);
        
        // Type
        $value = $this->getElement(self::ELEMENT_TYPE)->getValue();
        $document->setType($value);

        $datesHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('Dates');
        
        // CompletedDate
        $value = $this->getElement(self::ELEMENT_COMPLETED_DATE)->getValue();
        $date = $datesHelper->getOpusDate($value);        
        $document->setCompletedDate($date);
        
        // CompletedYear
        $value = $this->getElement(self::ELEMENT_COMPLETED_YEAR)->getValue();
        $document->setCompletedYear($value);
        
        // PublishedDate
        $value = $this->getElement(self::ELEMENT_PUBLISHED_DATE)->getValue();
        $date = $datesHelper->getOpusDate($value);        
        $document->setPublishedDate($date);
        
        // PublishedYear
        $value = $this->getElement(self::ELEMENT_PUBLISHED_YEAR)->getValue();
        $document->setPublishedYear($value);
    }
    
}