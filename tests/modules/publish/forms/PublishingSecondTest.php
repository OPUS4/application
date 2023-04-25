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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Publish_Form_PublishingSecondTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['view', 'translation'];

    /** @var Zend_Log */
    protected $logger;

    public function setUp(): void
    {
        $writer       = new Zend_Log_Writer_Null();
        $this->logger = new Zend_Log($writer);
        parent::setUp();
    }

    /**
     * exception because of missing session documentType
     */
    public function testConstructorWithoutDocTypeInSession()
    {
        $this->expectException(Publish_Model_FormSessionTimeoutException::class);
        $form = new Publish_Form_PublishingSecond($this->logger);
    }

    /**
     * A sucessful creation of PublishingSecond should result in having at least two buttons send and back
     */
    public function testConstructorWithDocTypeInSession()
    {
        $session               = new Zend_Session_Namespace('Publish');
        $session->documentType = 'preprint';
        $form                  = new Publish_Form_PublishingSecond($this->logger);
        $this->assertNotNull($form->getElement('back'));
        $this->assertNotNull($form->getElement('send'));
    }

    /**
     * Data is invalid because doc type workingpaper need more field entries.
     */
    public function testIsValidWithInvalidData()
    {
        $config                         = $this->getConfig();
        $config->documentTypes->include = 'all,preprint,article,demo,workingpaper';
        $session                        = new Zend_Session_Namespace('Publish');
        $session->documentType          = 'workingpaper';
        $form                           = new Publish_Form_PublishingSecond($this->logger);
        $data                           = [
            'PersonSubmitterFirstName_1' => 'John',
            'PersonSubmitterLastName_1'  => 'Doe',
        ];

        $valid = $form->isValid($data);
        $this->assertFalse($valid);
    }

    /**
     * Doc Type has only two fields which are already filled.
     */
    public function testIsValidWithValidData()
    {
        $config                         = $this->getConfig();
        $config->documentTypes->include = 'all,preprint,article,demo,workingpaper';
        $session                        = new Zend_Session_Namespace('Publish');
        $session->documentType          = 'demo';
        $form                           = new Publish_Form_PublishingSecond($this->logger);
        $data                           = [
            'PersonSubmitterFirstName_1' => 'John',
            'PersonSubmitterLastName_1'  => 'Doe',
        ];

        $valid = $form->isValid($data);
        $this->assertTrue($valid);
    }

    /**
     * Demo has 2 fields which are stored in elements and 2 new buttons are created.
     */
    public function testPrepareCheckMethodWithDemoType()
    {
        $config                         = $this->getConfig();
        $config->documentTypes->include = 'all,preprint,article,demo,workingpaper';
        $session                        = new Zend_Session_Namespace('Publish');
        $session->documentType          = 'demo';

        $form = new Publish_Form_PublishingSecond($this->logger);
        $data = [
            'PersonSubmitterFirstName_1' => 'John',
            'PersonSubmitterLastName_1'  => 'Doe',
        ];
        $form->prepareCheck();
        $this->assertNotNull($form->getElement('back'));
        $this->assertNotNull($form->getElement('send'));
        $this->assertTrue($session->elements['PersonSubmitterFirstName_1']['value'] === 'John');
        $this->assertTrue($session->elements['PersonSubmitterLastName_1']['value'] === 'Doe');
    }

    public function testExternalElementLegalNotices()
    {
        $session                   = new Zend_Session_Namespace('Publish');
        $session->documentType     = 'all';
        $session->additionalFields = [];

        $elementData = [
            'id'          => 'LegalNotices',
            'label'       => 'LegalNotices',
            'req'         => 'required',
            'type'        => 'Zend_Form_Element_Checkbox',
            'createType'  => 'checkbox',
            'header'      => 'header_LegalNotices',
            'value'       => '0',
            'check'       => '',
            'disabled'    => '0',
            'error'       => [],
            'DT_external' => true,
        ];

        $session->DT_externals['LegalNotices'] = $elementData;

        $form = new Publish_Form_PublishingSecond($this->logger);
        $this->assertNotNull($form->getElement('LegalNotices'));
    }
}
