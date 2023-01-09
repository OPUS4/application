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

class Publish_Model_ExtendedValidationTest extends ControllerTestCase
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

    public function testPersonsFirstNamesWithInvalidData()
    {
        $session               = new Zend_Session_Namespace('Publish');
        $session->documentType = 'all';
        $form                  = new Publish_Form_PublishingSecond($this->logger);
        $data                  = [
            'PersonSubmitterFirstName__1'     => '',
            'PersonSubmitterLastName_1'       => 'Doe',
            'TitleMain_1'                     => 'Entenhausen',
            'TitleMainLanguage_1'             => 'deu',
            'PersonAuthorFirstName_1'         => 'Vorname',
            'PersonAuthorLastName_1'          => '',
            'PersonAuthorEmail_1'             => '',
            'PersonAuthorAllowEmailContact_1' => '0',
            'CompletedDate'                   => '12.06.2012',
            'Language'                        => 'deu',
            'Licence'                         => '4',
        ];

        $val    = new Publish_Model_ExtendedValidation($form, $data, $this->logger, $session);
        $result = $val->validate();
        $this->assertFalse($result);
    }

    public function testPersonsEmailWithInvalidData()
    {
        $session               = new Zend_Session_Namespace('Publish');
        $session->documentType = 'all';
        $form                  = new Publish_Form_PublishingSecond($this->logger);
        $data                  = [
            'PersonSubmitterFirstName__1'     => 'John',
            'PersonSubmitterLastName_1'       => 'Doe',
            'TitleMain_1'                     => 'Entenhausen',
            'TitleMainLanguage_1'             => 'deu',
            'PersonAuthorFirstName_1'         => '',
            'PersonAuthorLastName_1'          => '',
            'PersonAuthorEmail_1'             => 'egal@wurscht.de',
            'PersonAuthorAllowEmailContact_1' => '',
            'CompletedDate'                   => '06.09.2011',
            'Language'                        => 'deu',
            'Licence'                         => '4',
        ];

        $val    = new Publish_Model_ExtendedValidation($form, $data, $this->logger, $session);
        $result = $val->validate();
        $this->assertFalse($result);
    }

    public function testPersonsEmailNotificationWithValidData()
    {
        $config                         = $this->getConfig();
        $config->documentTypes->include = 'all,preprint,article,demo,workingpaper';
        $session                        = new Zend_Session_Namespace('Publish');
        $session->documentType          = 'workingpaper';
        $form                           = new Publish_Form_PublishingSecond($this->logger);
        $data                           = [
            'PersonSubmitterFirstName_1'      => 'John',
            'PersonSubmitterLastName_1'       => 'Doe',
            'TitleMain_1'                     => 'Entenhausen',
            'TitleMainLanguage_1'             => 'deu',
            'PersonAuthorFirstName_1'         => '',
            'PersonAuthorLastName_1'          => 'Tester',
            'PersonAuthorEmail_1'             => 'egal@wurscht.de',
            'PersonAuthorAllowEmailContact_1' => '0',
            'CompletedDate'                   => '06.09.2011',
            'Language'                        => 'deu',
            'Licence'                         => '4',
        ];

        $val    = new Publish_Model_ExtendedValidation($form, $data, $this->logger, $session);
        $result = $val->validate();
        $this->assertTrue($result);
    }

    public function testPersonsEmailNotificationWithInvalidData()
    {
        $config                         = $this->getConfig();
        $config->documentTypes->include = 'all,preprint,article,demo,workingpaper';
        $session                        = new Zend_Session_Namespace('Publish');
        $session->documentType          = 'workingpaper';
        $form                           = new Publish_Form_PublishingSecond($this->logger);
        $data                           = [
            'PersonSubmitterFirstName_1'      => 'John',
            'PersonSubmitterLastName_1'       => 'Doe',
            'TitleMain_1'                     => 'Entenhausen',
            'TitleMainLanguage_1'             => 'deu',
            'PersonAuthorFirstName_1'         => '',
            'PersonAuthorLastName_1'          => 'Tester',
            'PersonAuthorEmail_1'             => '',
            'PersonAuthorAllowEmailContact_1' => '1',
            'CompletedDate'                   => '06.09.2011',
            'Language'                        => 'deu',
            'Licence'                         => '4',
        ];

        $val    = new Publish_Model_ExtendedValidation($form, $data, $this->logger, $session);
        $result = $val->validate();
        $this->assertFalse($result);
    }

    /**
     * Test, if validation fails if Language is deu and TitleMainLanguage is eng
     */
    public function testMainTitleWithWrongDocLanguage()
    {
        $config                         = $this->getConfig();
        $config->documentTypes->include = 'all,preprint,article,demo,workingpaper';
        $session                        = new Zend_Session_Namespace('Publish');
        $session->documentType          = 'workingpaper';
        $form                           = new Publish_Form_PublishingSecond($this->logger);
        $data                           = [
            'PersonSubmitterFirstName_1'      => 'John',
            'PersonSubmitterLastName_1'       => 'Doe',
            'TitleMain_1'                     => 'Entenhausen',
            'TitleMainLanguage_1'             => 'eng',
            'PersonAuthorFirstName_1'         => '',
            'PersonAuthorLastName_1'          => 'Tester',
            'PersonAuthorEmail_1'             => '',
            'PersonAuthorAllowEmailContact_1' => '0',
            'CompletedDate'                   => '11.06.2012',
            'Language'                        => 'deu',
            'Licence'                         => '4',
        ];

        $val    = new Publish_Model_ExtendedValidation($form, $data, $this->logger, $session);
        $result = $val->validate();
        $this->assertFalse($result);
    }

    /**
     * Test, if validation is successful if title main language is empty - "Sprache der Veröffentlichung übernehmen"
     */
    public function testEmptyMainTitleLanguage()
    {
        $config                         = $this->getConfig();
        $config->documentTypes->include = 'all,preprint,article,demo,workingpaper';
        $session                        = new Zend_Session_Namespace('Publish');
        $session->documentType          = 'workingpaper';
        $form                           = new Publish_Form_PublishingSecond($this->logger);
        $data                           = [
            'PersonSubmitterFirstName_1'      => 'John',
            'PersonSubmitterLastName_1'       => 'Doe',
            'TitleMain_1'                     => 'Entenhausen',
            'TitleMainLanguage_1'             => '',
            'PersonAuthorFirstName_1'         => '',
            'PersonAuthorLastName_1'          => 'Tester',
            'PersonAuthorEmail_1'             => '',
            'PersonAuthorAllowEmailContact_1' => '0',
            'CompletedDate'                   => '11.06.2012',
            'Language'                        => 'deu',
            'Licence'                         => '4',
        ];

        $val    = new Publish_Model_ExtendedValidation($form, $data, $this->logger, $session);
        $result = $val->validate();
        $this->assertTrue($result);
    }

    /**
     * Test, if validation is successful for several title main languages
     * Last title main has the document language (deu)
     */
    public function testSeveralMainTitleLanguages()
    {
        $this->markTestSkipped('Method getExtendedForm removed from Form class: moved to FormController class as manipulateSession');

        $config                                 = $this->getConfig();
        $config->documentTypes->include         = 'all,preprint,article,demo,workingpaper';
        $session                                = new Zend_Session_Namespace('Publish');
        $session->documentType                  = 'workingpaper';
        $session->additionalFields              = [];
        $session->additionalFields['TitleMain'] = '4';
        $form                                   = new Publish_Form_PublishingSecond($this->logger);
        $data                                   = [
            'PersonSubmitterFirstName_1'      => 'John',
            'PersonSubmitterLastName_1'       => 'Doe',
            'TitleMain_1'                     => 'Entenhausen',
            'TitleMainLanguage_1'             => 'spa',
            'TitleMain_2'                     => 'Entenhausen2',
            'TitleMainLanguage_2'             => 'eng',
            'TitleMain_3'                     => 'Entenhausen3',
            'TitleMainLanguage_3'             => 'fra',
            'TitleMain_4'                     => 'Entenhausen4',
            'TitleMainLanguage_4'             => 'deu',
            'PersonAuthorFirstName_1'         => '',
            'PersonAuthorLastName_1'          => 'Tester',
            'PersonAuthorEmail_1'             => '',
            'PersonAuthorAllowEmailContact_1' => '0',
            'CompletedDate'                   => '14.06.2012',
            'Language'                        => 'deu',
            'Licence'                         => '4',
        ];

        $form->getExtendedForm($data, false); // method does not exist!
        $val    = new Publish_Model_ExtendedValidation($form, $data, $this->logger, $session);
        $result = $val->validate();
        $this->assertTrue($result);
    }

    /**
     * Test, if validation is successful for diferent types of title languages
     * Only main title may be validated with document language (deu), the languages of the other titles must be ignored
     */
    public function testSeveralTitleTypeLanguages()
    {
        $config                         = $this->getConfig();
        $config->documentTypes->include = 'all,preprint,article,demo,workingpaper';
        $session                        = new Zend_Session_Namespace('Publish');
        $session->documentType          = 'workingpaper';
        $form                           = new Publish_Form_PublishingSecond($this->logger);
        $data                           = [
            'PersonSubmitterFirstName_1'      => 'John',
            'PersonSubmitterLastName_1'       => 'Doe',
            'TitleMain_1'                     => 'Entenhausen',
            'TitleMainLanguage_1'             => 'deu',
            'TitleAbstract_1'                 => 'rus Title',
            'TitleAbstractLanguage_1'         => 'rus',
            'TitleSub_1'                      => 'spa title',
            'TitleSubLanguage_1'              => 'spa',
            'TitleAdditional_1'               => 'mul title',
            'TitleAdditionalLanguage_1'       => 'mul',
            'PersonAuthorFirstName_1'         => '',
            'PersonAuthorLastName_1'          => 'Tester',
            'PersonAuthorEmail_1'             => '',
            'PersonAuthorAllowEmailContact_1' => '0',
            'CompletedDate'                   => '11.06.2012',
            'Language'                        => 'deu',
            'Licence'                         => '4',
        ];

        $val    = new Publish_Model_ExtendedValidation($form, $data, $this->logger, $session);
        $result = $val->validate();
        $this->assertTrue($result);
    }

    /**
     * Regression test for OPUSVIER-2635
     */
    public function testSeriesNumberValidationWithUnknownSeries()
    {
        $config                         = $this->getConfig();
        $config->documentTypes->include = 'all';
        $session                        = new Zend_Session_Namespace('Publish');
        $session->documentType          = 'all';
        $form                           = new Publish_Form_PublishingSecond($this->logger);
        $data                           = [
            'PersonSubmitterFirstName_1' => 'John',
            'PersonSubmitterLastName_1'  => 'Doe',
            'TitleMain_1'                => 'Entenhausen',
            'TitleMainLanguage_1'        => 'deu',
            'Licence'                    => '4',
            'Series_1'                   => '123456-doesnotexist',
            'SeriesNumber_1'             => '123',
        ];

        $val    = new Publish_Model_ExtendedValidation($form, $data, $this->logger, $session);
        $result = $val->validate();
        $this->assertTrue($result);
    }

    /**
     * Regression test for OPUSVIER-2635
     */
    public function testSeriesNumberValidationWithMissingSeriesField()
    {
        $config                         = $this->getConfig();
        $config->documentTypes->include = 'all';
        $session                        = new Zend_Session_Namespace('Publish');
        $session->documentType          = 'all';
        $form                           = new Publish_Form_PublishingSecond($this->logger);
        $data                           = [
            'PersonSubmitterFirstName_1' => 'John',
            'PersonSubmitterLastName_1'  => 'Doe',
            'TitleMain_1'                => 'Entenhausen',
            'TitleMainLanguage_1'        => 'deu',
            'Licence'                    => '4',
            'SeriesNumber_1'             => '123',
        ];

        $val    = new Publish_Model_ExtendedValidation($form, $data, $this->logger, $session);
        $result = $val->validate();
        $this->assertFalse($result);
    }
}
