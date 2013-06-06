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
 * Unterformular zum Editieren einer Zusammenfassung (abstract). 
 */
class Admin_Form_DocumentAbstract extends Admin_Form_AbstractModelSubForm {
    
    const ELEMENT_ID = 'Id';
    
    const ELEMENT_LANGUAGE = 'Language';
    
    const ELEMENT_VALUE = 'Value';

    public function init() {
        parent::init();
        
        $elementFactory = new Admin_Model_FormElementFactory();
        
        $element = new Zend_Form_Element_Hidden(self::ELEMENT_ID);
        $this->addElement($element);
        
        $element = $elementFactory->createLanguageSelect(self::ELEMENT_LANGUAGE);
        $element->setLabel('Language'); // TODO translate
        $this->addElement($element); 
        
        $element = new Zend_Form_Element_Textarea(self::ELEMENT_VALUE);
        $element->setLabel('Text');
        $element->setRequired(true);
        $this->addElement($element);
   }
    
    public function populateFromModel($abstract) {
        $this->getElement(self::ELEMENT_ID)->setValue($abstract->getId());
        $this->getElement(self::ELEMENT_LANGUAGE)->setValue($abstract->getLanguage());
        $this->getElement(self::ELEMENT_VALUE)->setValue($abstract->getValue());
    }
    
    public function updateModel($abstract) {
        $abstract->setLanguage($this->getElement(self::ELEMENT_LANGUAGE)->getValue());
        $abstract->setValue($this->getElement(self::ELEMENT_VALUE)->getValue());
    }

    public function getModel() {
        $abstractId = $this->getElement(self::ELEMENT_ID)->getValue();
        
        if (empty($abstractId)) {
            $abstractId = null;
        }
        
        $abstract = new Opus_TitleAbstract($abstractId);
        
        $this->updateModel($abstract);
        
        return $abstract;
    }
    
    public function loadDefaultDecorators() {
        parent::loadDefaultDecorators();
        
        $this->removeDecorator('Fieldset');
    }

}
