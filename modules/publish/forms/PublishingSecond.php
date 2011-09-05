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
 * @package     Module_Publish
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Shows a publishing form for new documents
 *
 */
class Publish_Form_PublishingSecond extends Zend_Form {

    public $doctype = "";      
    public $additionalFields = array();
    public $postData = array();
    public $log;    
    public $msc = array();
    public $session;
    public $helper;
    public $view;

    public function __construct($view, $postData=null, $options=null) {
        $this->session = new Zend_Session_Namespace('Publish');
        $this->log = Zend_Registry::get('Zend_Log');
        $this->doctype = $this->session->documentType;               
        $this->additionalFields = $this->session->additionalFields;
        $this->postData = $postData;
        
        $this->view = $view;
        if (is_null($this->view))
                throw new Publish_Model_NoViewFoundException();
        
        $this->helper = new Publish_Model_FormHelper($this->view, $this);

        parent::__construct($options);
        $this->setSecondFormViewVariables($this);
    }

    /**
     * Overwritten method isValid to support extended validation
     * @param <type> $data 
     */
    public function isValid($data) {
        $extended = new Publish_Model_ExtendedValidation($this, $data);
        $valid1 = $extended->validate();

        $valid2 = parent::isValid($extended->data);

        $valid3 = $extended->validate();
        //inherit data changes during validation
        $this->populate($extended->data);
        $this->postData = $extended->data;

        if ($valid1 && $valid2 && $valid3) {
            $this->session->invalidForm = '0';
            return true;
        }
        else {
            $this->session->invalidForm = '1';
            return false;
        }
    }

    /**
     * Build document publishing form that depends on the doc type
     * @param $doctype
     * @return void
     */
    public function init() {
        $dom = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes')->getDocument($this->doctype);        
        if (!isset($dom)) {
            throw new Publish_Model_FormSessionTimeoutException();
        }
        $parser = new Publish_Model_DocumenttypeParser($dom, $this);
        $this->log->debug("Parser created");
        $parser->setAdditionalFields($this->additionalFields);

        $parser->setPostValues($this->postData);

        if ($parser !== false)
            $parser->parse();

        $this->log->debug("Parsing ready");        
        $this->addElements($parser->getFormElements());
        $this->_addSubmit('button_label_send', 'send');
        $this->_addSubmit('button_label_back', 'back');

        if (isset($this->postData))
            $this->populate($this->postData);
    }

    /**
     * Adds submit button to the form.
     * @param <type> $label
     */
    public function _addSubmit($label, $name) {
        //Submit button
        $submit = $this->createElement('submit', $name);
        $submit->setLabel($label);
        $this->addElement($submit);
    }

    /**
     *
     * @param <type> $elementName
     * @return string
     */
    public function getElementAttributes($elementName) {
        $elementAttributes = $this->helper->getElementAttributes($elementName);
        return $elementAttributes;
    }

    public function showTemplate() {
        return $this->helper->showTemplate();
    }

    public function showCheckpage() {
        return $this->helper->showCheckPage();
    }

    public function getExtendedForm($postData, $reload) {
        return $this->helper->getExtendedForm($postData, $reload);
    }

    public function setSecondFormViewVariables() {
        $this->helper->setSecondFormViewVariables();
    }

}
