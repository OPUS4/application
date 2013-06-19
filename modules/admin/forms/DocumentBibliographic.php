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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Unterformular fuer weitere Metadaten eines Dokuments.
 */
class Admin_Form_DocumentBibliographic extends Admin_Form_DocumentSection {

    const ELEMENT_CONTRIBUTING_CORPORATION = 'ContributingCorporation';
    
    const ELEMENT_CREATING_CORPORATION = 'CreatingCorporation';
    
    const ELEMENT_EDITION = 'Edition';
    
    const ELEMENT_ISSUE = 'Issue';
    
    const ELEMENT_PAGE_FIRST = 'PageFirst';
    
    const ELEMENT_PAGE_LAST = 'PageLast';
    
    const ELEMENT_PAGE_COUNT = 'PageCount';
    
    const ELEMENT_PUBLISHER_NAME = 'PublisherName';
    
    const ELEMENT_PUBLISHER_PLACE = 'PublisherPlace';
    
    const ELEMENT_VOLUME = 'Volume';
    
    const ELEMENT_THESIS_DATE_ACCEPTED = 'ThesisDateAccepted';
    
    const ELEMENT_THESIS_YEAR_ACCEPTED = 'ThesisYearAccepted';
    
    const ELEMENT_BELONGS_TO_BIBLIOGRAPHY = 'BelongsToBibliography';
    
    public function init() {
        parent::init();
        
        $this->setLegend('admin_document_section_bibliographic');
        
        // Label entsprechen den Namen der Elemente
        $this->addTextElement(self::ELEMENT_EDITION);
        $this->addTextElement(self::ELEMENT_VOLUME);
        $this->addTextElement(self::ELEMENT_PUBLISHER_NAME);
        $this->addTextElement(self::ELEMENT_PUBLISHER_PLACE);

        $element = $this->addTextElement(self::ELEMENT_PAGE_COUNT);
        $element->addValidator('Int');
        $element->addValidator(new Zend_Validate_GreaterThan(0));
        
        $element = $this->addTextElement(self::ELEMENT_PAGE_FIRST);
        $element->addValidator('Int');
        $element->addValidator(new Zend_Validate_GreaterThan(-1));
        
        $element = $this->addTextElement(self::ELEMENT_PAGE_LAST);
        $element->addValidator('Int');
        $element->addValidator(new Zend_Validate_GreaterThan(-1));
        
        $this->addTextElement(self::ELEMENT_ISSUE);
        $this->addTextElement(self::ELEMENT_CONTRIBUTING_CORPORATION);
        $this->addTextElement(self::ELEMENT_CREATING_CORPORATION);
        
        $elementFactory = new Admin_Model_FormElementFactory();
        
        $element = $elementFactory->createDateElement(self::ELEMENT_THESIS_DATE_ACCEPTED);
        $this->addElement($element);
        
        $element = $elementFactory->createYearElement(self::ELEMENT_THESIS_YEAR_ACCEPTED);
        $this->addElement($element);

        $this->addSubForm(new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentPublisher', 'ThesisPublisher'), 'Publishers');
        $this->addSubForm(new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentGrantor', 'ThesisGrantor'), 'Grantors');
        
        $element = new Zend_Form_Element_Checkbox(self::ELEMENT_BELONGS_TO_BIBLIOGRAPHY);
        $element->setLabel('BelongsToBibliography');
        $this->addElement($element);
    }
    
    public function populateFromModel($document) {
        parent::populateFromModel($document);
        
        $datesHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('Dates');
        
        $this->getElement(self::ELEMENT_CONTRIBUTING_CORPORATION)->setValue($document->getContributingCorporation());
        $this->getElement(self::ELEMENT_CREATING_CORPORATION)->setValue($document->getCreatingCorporation());
        $this->getElement(self::ELEMENT_EDITION)->setValue($document->getEdition());
        $this->getElement(self::ELEMENT_ISSUE)->setValue($document->getIssue());
        $this->getElement(self::ELEMENT_PAGE_FIRST)->setValue($document->getPageFirst());
        $this->getElement(self::ELEMENT_PAGE_LAST)->setValue($document->getPageLast());
        $this->getElement(self::ELEMENT_PAGE_COUNT)->setValue($document->getPageNumber());
        $this->getElement(self::ELEMENT_PUBLISHER_NAME)->setValue($document->getPublisherName());
        $this->getElement(self::ELEMENT_PUBLISHER_PLACE)->setValue($document->getPublisherPlace());
        $this->getElement(self::ELEMENT_VOLUME)->setValue($document->getVolume());

        $date = $datesHelper->getDateString($document->getThesisDateAccepted());
        $this->getElement(self::ELEMENT_THESIS_DATE_ACCEPTED)->setValue($date);
        $this->getElement(self::ELEMENT_THESIS_YEAR_ACCEPTED)->setValue($document->getThesisYearAccepted());
        $this->getElement(self::ELEMENT_BELONGS_TO_BIBLIOGRAPHY)->setValue($document->getBelongsToBibliography());
    }
    
    public function updateModel($document) {
        parent::updateModel($document);
        
        $datesHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('Dates');
        
        $document->setContributingCorporation($this->getElementValue(self::ELEMENT_CONTRIBUTING_CORPORATION));
        $document->setCreatingCorporation($this->getElementValue(self::ELEMENT_CREATING_CORPORATION));
        $document->setEdition($this->getElementValue(self::ELEMENT_EDITION));
        $document->setIssue($this->getElementValue(self::ELEMENT_ISSUE));
        $document->setPageFirst($this->getElementValue(self::ELEMENT_PAGE_FIRST));
        $document->setPageLast($this->getElementValue(self::ELEMENT_PAGE_LAST));
        $document->setPageNumber($this->getElementValue(self::ELEMENT_PAGE_COUNT));
        $document->setPublisherName($this->getElementValue(self::ELEMENT_PUBLISHER_NAME));
        $document->setPublisherPlace($this->getElementValue(self::ELEMENT_PUBLISHER_PLACE));
        $document->setVolume($this->getElementValue(self::ELEMENT_VOLUME));
        
        $value = $this->getElementValue(self::ELEMENT_THESIS_DATE_ACCEPTED);
        $date = (is_null($value)) ? null : $datesHelper->getOpusDate($value);        
        $document->setThesisDateAccepted($date);
        
        $document->setThesisYearAccepted($this->getElementValue(self::ELEMENT_THESIS_YEAR_ACCEPTED));
        $document->setBelongsToBibliography($this->getElementValue(self::ELEMENT_BELONGS_TO_BIBLIOGRAPHY));
    }
    
    /**
     * 
     * @param type $name
     * @param type $label
     * @return \Zend_Form_Element_Text
     * 
     * TODO move to parent/other class?
     */
    public function addTextElement($name, $label = null) {
        if (is_null($label)) {
            $label = $name;
        }
        
        $element = new Zend_Form_Element_Text($name);
        $element->setLabel($label);
        $this->addElement($element);
        
        return $element;
    }

}
