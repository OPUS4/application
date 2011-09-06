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
 * @package     Module_Publish Unit Test
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Publish_Model_DocumenttypeParserTest extends ControllerTestCase {

         
    /**
     * @expectedException Application_Exception
     */
    public function testConstructorWithWrongDom() {
        $dom = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes')->getDocument('irgendwas');         
        $model = new Publish_Model_DocumenttypeParser($dom, null);
        $this->assertNull($model->dom);
    }
    
    
    public function testConstructorWithCorrectDom() {
        $dom = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes')->getDocument('preprint');         
        $model = new Publish_Model_DocumenttypeParser($dom, null);
        $this->assertType('DOMDocument', $model->dom);
    }    
    
    /**
     * @expectedException Publish_Model_FormSessionTimeoutException
     */
    public function testConstructorWithCorrectDomAndWrongForm() {
        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'irgendwas';
        $dom = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes')->getDocument('preprint');         
        $form = new Publish_Form_PublishingSecond(new Zend_View());
        $model = new Publish_Model_DocumenttypeParser($dom, $form);        
        $this->assertType('DOMDocument', $model->dom);        
    }
    
    public function testConstructorWithCorrectDomAndCorrectForm() {
        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'preprint';
        $dom = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes')->getDocument('preprint');         
        $form = new Publish_Form_PublishingSecond(new Zend_View());
        $model = new Publish_Model_DocumenttypeParser($dom, $form);        
        $this->assertType('DOMDocument', $model->dom);
        $this->assertType('Publish_Form_PublishingSecond', $model->form);
    }
    
    
}
