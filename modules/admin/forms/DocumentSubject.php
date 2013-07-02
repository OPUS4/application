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
 * Unterformular fuer das Editieren eines Stichwortes.
 */
class Admin_Form_DocumentSubject extends Admin_Form_AbstractModelSubForm {

    const ELEMENT_ID = 'Id';
    
    const ELEMENT_LANGUAGE = 'Language';
    
    const ELEMENT_VALUE = 'Value';
    
    const ELEMENT_EXTERNAL_KEY = 'ExternalKey';
    
    private $__subjectType;
    
    private $__language;
    
    public function __construct($type, $language = null, $options = null) {
        $this->__subjectType = $type;
        $this->__language = $language;
        parent::__construct($options);
    }
    
    public function init() {
        parent::init();
        
        $this->addElement('Hidden', self::ELEMENT_ID);
        
        if (is_null($this->__language)) {
            $element = $this->createElement('Language', self::ELEMENT_LANGUAGE, array('label' => 'Language'));
        }
        else {
            $element = $this->createElement('Hidden', self::ELEMENT_LANGUAGE, array('value' => $this->__language));
        }
        $this->addElement($element);
        
        $this->addElement('Text', self::ELEMENT_VALUE, array('required' => true, 'label' => 'Value'));
        $this->addElement('Text', self::ELEMENT_EXTERNAL_KEY, array('label' => 'ExternalKey'));
    }
    
    public function populateFromModel($subject) {
        $this->getElement(self::ELEMENT_ID)->setValue($subject->getId());
        $this->getElement(self::ELEMENT_LANGUAGE)->setValue($subject->getLanguage());
        $this->getElement(self::ELEMENT_VALUE)->setValue($subject->getValue());
        $this->getElement(self::ELEMENT_EXTERNAL_KEY)->setValue($subject->getExternalKey());
    }
    
    public function updateModel($subject) {
        $subject->setLanguage($this->getElement(self::ELEMENT_LANGUAGE)->getValue());
        $subject->setValue($this->getElement(self::ELEMENT_VALUE)->getValue());
        $subject->setExternalKey($this->getElement(self::ELEMENT_EXTERNAL_KEY)->getValue());
        $subject->setType($this->__subjectType);
    }

    public function getModel() {
        $subjectId = $this->getElement(self::ELEMENT_ID)->getValue();
        
        if (empty($subjectId)) {
            $subjectId = null;
        }
        
        $subject = new Opus_Subject($subjectId);

        $this->updateModel($subject);
                
        return $subject;
    }
    
    public function loadDefaultDecorators() {
        parent::loadDefaultDecorators();
        
        $this->removeDecorator('Fieldset');
    }
    
}
