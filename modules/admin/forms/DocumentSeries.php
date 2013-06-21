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
 * Unterformular fuer das Editieren eines Serieneintrags.
 * 
 * TODO gibt es gute Lösung die Doc-ID nicht noch einmal im Unterformular zu haben (als Teil der ID)
 */
class Admin_Form_DocumentSeries extends Admin_Form_AbstractModelSubForm {
    
    const ELEMENT_DOC_ID = 'Id';
    
    const ELEMENT_SERIES_ID = 'SeriesId';
    
    const ELEMENT_NUMBER = 'Number';
    
    const ELEMENT_SORT_ORDER = 'SortOrder';
    
    public function init() {
        parent::init();
        
        // Schluessel fuer Link Objekte ist Dokument-ID + Series-ID
        $element = new Form_Element_Hidden(self::ELEMENT_DOC_ID);
        $this->addElement($element);
        
        $element = $this->_createSeriesSelect(self::ELEMENT_SERIES_ID);
        $element->addValidator('Int');
        $element->setRequired(true);
        $this->addElement($element);
        
        $element = new Form_Element_Text(self::ELEMENT_NUMBER);
        $element->setRequired(true);
        $this->addElement($element);
        
        $element = new Form_Element_Text(self::ELEMENT_SORT_ORDER);
        $element->addValidator('Int');
        $this->addElement($element);
    }
    
    public function populateFromModel($seriesLink) {
        $linkId = $seriesLink->getId();
        $this->getElement(self::ELEMENT_DOC_ID)->setValue($linkId[0]);
        $series = $seriesLink->getModel();
        $this->getElement(self::ELEMENT_SERIES_ID)->setValue($series->getId());
        $this->getElement(self::ELEMENT_NUMBER)->setValue($seriesLink->getNumber());
        $this->getElement(self::ELEMENT_SORT_ORDER)->setValue($seriesLink->getDocSortOrder());
    }
    
    public function updateModel($seriesLink) {
        $seriesId = $this->getElement(self::ELEMENT_SERIES_ID)->getValue();
        $series = new Opus_Series($seriesId);
        $seriesLink->setModel($series);
        $seriesLink->setNumber($this->getElement(self::ELEMENT_NUMBER)->getValue());
        $seriesLink->setDocSortOrder($this->getElement(self::ELEMENT_SORT_ORDER)->getValue());
    }

    public function getModel() {
        $docId = $this->getElement(self::ELEMENT_DOC_ID)->getValue();
        
        if (empty($docId)) {
            $linkId = null;
        }
        else {
            $seriesId = $this->getElement(self::ELEMENT_SERIES_ID)->getValue();
            $linkId = array($docId, $seriesId);
        }
        
        try {
            $seriesLink = new Opus_Model_Dependent_Link_DocumentSeries($linkId);
        }
        catch (Opus_Model_NotFoundException $omnfe) {
            $seriesLink = new Opus_Model_Dependent_Link_DocumentSeries();
        }
        
        $this->updateModel($seriesLink);
        
        return $seriesLink;
    }
    
    /**
     * 
     * @param type $name
     * @return \Zend_Form_Element_Select
     * TODO move somewhere else?
     */
    protected function _createSeriesSelect($name) {
        $select = new Zend_Form_Element_Select($name);
        
        $options = Opus_Series::getAll();
        
        foreach ($options as $option) {
            $select->addMultiOption($option->getId(), $option->getTitle());
        }
        
        return $select;
    }

}
