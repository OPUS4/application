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
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
class Publish_Model_FormElementTest extends ControllerTestCase
{

    protected $additionalResources = ['view', 'translation'];

    protected $_logger;

    public function setUp()
    {
        $writer = new Zend_Log_Writer_Null;
        $this->_logger = new Zend_Log($writer);
        parent::setUp();
    }

    public function testUnrequiredFirstNames()
    {
        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'all';

        $form = new Publish_Form_PublishingSecond($this->_logger);
        $name = 'PersonAuthor';
        $required = true;
        $formElement = 'text';
        $datatype = 'Person';
        $multiplicity = 1;

        $element = new Publish_Model_FormElement($form, $name, $required, $formElement, $datatype, $multiplicity);
        $element->initGroup();
        $subformElements = $element->getSubFormElements();

        foreach ($subformElements as $sub) {
            /* @var $sub Zend_Form_Element */
            if ($sub->getName() == 'PersonAuthorFirstName') {
                $this->assertFalse($sub->isRequired());
            }
        }
    }
}
