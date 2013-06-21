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
class Admin_Form_DocumentEnrichment extends Admin_Form_AbstractModelSubForm {
    
    const ELEMENT_ID = 'Id';
    
    const ELEMENT_KEY_NAME = 'KeyName';
    
    const ELEMENT_VALUE = 'Value';
    
    public function init() {
        parent::init();
        
        $element = new Form_Element_Hidden(self::ELEMENT_ID);
        $this->addElement($element);
        
        $element = $this->_createEnrichmentKeySelect(self::ELEMENT_KEY_NAME);
        $this->addElement($element);
        
        $element = new Form_Element_Text(self::ELEMENT_VALUE);
        $element->setRequired(true);
        $this->addElement($element);
    }
    
    public function populateFromModel($enrichment) {
        $this->getElement(self::ELEMENT_ID)->setValue($enrichment->getId());
        $this->getElement(self::ELEMENT_KEY_NAME)->setValue($enrichment->getKeyName());
        $this->getElement(self::ELEMENT_VALUE)->setValue($enrichment->getValue());
    }
    
    public function updateModel($enrichment) {
        $enrichment->setKeyName($this->getElement(self::ELEMENT_KEY_NAME)->getValue());
        $enrichment->setValue($this->getElement(self::ELEMENT_VALUE)->getValue());
    }

    public function getModel() {
        $enrichmentId = $this->getElement(self::ELEMENT_ID)->getValue();
        
        if (empty($enrichmentId)) {
            $enrichmentId = null;
        }
        
        $enrichment = new Opus_Enrichment($enrichmentId);
        
        $this->updateModel($enrichment);
        
        return $enrichment;
    }
    
    protected function _createEnrichmentKeySelect($name = 'KeyName') {
        $select = new Form_Element_Select($name);
        
        $enrichment = new Opus_Enrichment();
        $options = $enrichment->getField('KeyName')->getDefault();
        
        foreach ($options as $index => $option) {
            $select->addMultiOption($option->getName(), $option->getName());
        }
        
        return $select;
    }
    
    public function loadDefaultDecorators() {
        parent::loadDefaultDecorators();
        
        $this->removeDecorator('Fieldset');
    }

}
