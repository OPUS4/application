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
 * @category    Application Unit Tests
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Test for class Application_Controller_Action_Helper_Translation and translations in general.
 */
class Application_Controller_Action_Helper_TranslationTest extends ControllerTestCase {

    /**
     * Translation resource for tests.
     * @var Zend_Translate
     */
    private $translate;

    /**
     * Translation controller helper for tests.
     * @var Application_Controller_Action_Helper_Translation
     */
    private $helper;

    public function setUp() {
        parent::setUp();

        $this->translate = Zend_Registry::get('Zend_Translate');
        $this->helper = Zend_Controller_Action_HelperBroker::getStaticHelper('Translation');
    }

    /**
     * Tests that the generated key is formatted correctly.
     */
    public function testGetKeyForValue() {
        $this->assertEquals('Opus_Document_ServerState_Value_Unpublished',
                $this->helper->getKeyForValue('Opus_Document', 'ServerState',
                        'unpublished'));
    }

    public function testGetKeyForValueOfDocumentType() {
        $this->assertEquals('testdoctype',
                $this->helper->getKeyForValue('Opus_Document', 'Type',
                        'testdoctype'));
    }

    public function testGetKeyForValueOfDocumentLanguage() {
        $this->assertEquals('testdoclang',
                $this->helper->getKeyForValue('Opus_Document', 'Language',
                        'testdoclang'));
    }

    public function testGetKeyForField() {
        $this->assertEquals('Language',
                $this->helper->getKeyForField('Opus_Document', 'Language'));
    }

    public function testGetKeyForTypeField() {
        $this->assertEquals('Opus_Document_Type',
                $this->helper->getKeyForField('Opus_Document', 'Type'));
    }

    public function testTranslationOfServerStateValues() {
        $doc = $this->createTestDocument();
        $values = $doc->getField('ServerState')->getDefault();

        foreach ($values as $value) {
            $key = $this->helper->getKeyForValue('Opus_Document', 'ServerState', $value);
            $this->assertNotEquals($key, $this->translate->translate($key),
                    'Translation key \'' . $key . '\' is missing.');
        }
    }

    public function testTranslationOfPersonRoleValues() {
        $model = new Opus_Model_Dependent_Link_DocumentPerson();
        $values = $model->getField('Role')->getDefault();

        foreach ($values as $value) {
            $key = $this->helper->getKeyForValue('Opus_Person', 'Role', $value);
            $this->assertNotEquals($key, $this->translate->translate($key),
                    'Translation key \'' . $key . '\' is missing.');
        }
    }

    public function testTranslationOfTitleTypeValues() {
        $model = new Opus_Title();
        $values = $model->getField('Type')->getDefault();

        foreach ($values as $value) {
            $key = $this->helper->getKeyForValue('Opus_Title', 'Type', $value);
            $this->assertNotEquals($key, $this->translate->translate($key),
                    'Translation key \'' . $key . '\' is missing.');
        }
    }

    public function testTranslationOfAbstractTypeValue() {
        $model = new Opus_TitleAbstract();
        $values = $model->getField('Type')->getDefault();

        foreach ($values as $value) {
            $key = $this->helper->getKeyForValue('Opus_TitleAbstract', 'Type', $value);
            $this->assertNotEquals($key, $this->translate->translate($key),
                    'Translation key \'' . $key . '\' is missing.');
        }
    }

    public function testTranslationOfIdentifierTypeValues() {
        $model = new Opus_Identifier();
        $values = $model->getField('Type')->getDefault();

        foreach ($values as $value) {
            $key = $this->helper->getKeyForValue('Opus_Identifier', 'Type', $value);
            $this->assertNotEquals($key, $this->translate->translate($key),
                    'Translation key \'' . $key . '\' is missing.');
        }
    }

    public function testTranslationOfReferenceTypeValues() {
        $model = new Opus_Reference();
        $values = $model->getField('Type')->getDefault();

        foreach ($values as $value) {
            $key = $this->helper->getKeyForValue('Opus_Reference', 'Type', $value);
            $this->assertNotEquals($key, $this->translate->translate($key),
                    'Translation key \'' . $key . '\' is missing.');
        }
    }

    public function testTranslationOfSubjectTypeValues() {
        $model = new Opus_Subject();
        $values = $model->getField('Type')->getDefault();

        foreach ($values as $value) {
            $key = $this->helper->getKeyForValue('Opus_Subject', 'Type', $value);
            $this->assertNotEquals($key, $this->translate->translate($key),
                    'Translation key \'' . $key . '\' is missing.');
        }
    }

    public function testTranslationOfNoteVisibilityValues() {
        $model = new Opus_Note();
        $values = $model->getField('Visibility')->getDefault();

        foreach ($values as $value) {
            $key = $this->helper->getKeyForValue('Opus_Note', 'Visibility', $value);
            $this->assertNotEquals($key, $this->translate->translate($key),
                    'Translation key \'' . $key . '\' is missing.');
        }
    }

    public function testTranslationOfOpusDocumentFields() {
        $model = $this->createTestDocument();

        $fieldNames = $model->describe();

        foreach ($fieldNames as $name) {
            $key = $this->helper->getKeyForField('Opus_Document', $name);
            $this->assertTrue($this->translate->isTranslated($key),
                    'Translation key \'' . $key . '\' is missing.');
        }
    }

    public function testTranslationOfOpusIdentifierFields() {
        $model = new Opus_Identifier();

        $fieldNames = $model->describe();

        foreach($fieldNames as $name) {
            if ($name == 'Status' || $name == 'RegistrationTs') {
                // do not provide translations for DOI specific fields
                continue;
            }
            $key = $this->helper->getKeyForField('Opus_Identifier', $name);
            $this->assertTrue($this->translate->isTranslated($key),
                    'Translation key \'' . $key . '\' is missing.');
        }
    }

    public function testTranslationOfOpusReferenceFields() {
        $model = new Opus_Reference();

        $fieldNames = $model->describe();

        foreach($fieldNames as $name) {
            $key = $this->helper->getKeyForField('Opus_Reference', $name);
            $this->assertTrue($this->translate->isTranslated($key),
                    'Translation key \'' . $key . '\' is missing.');
        }
    }

    public function testTranslationOfOpusTitleFields() {
        $model = new Opus_Title();

        $fieldNames = $model->describe();

        foreach($fieldNames as $name) {
            $key = $this->helper->getKeyForField('Opus_Title', $name);
            $this->assertTrue($this->translate->isTranslated($key),
                    'Translation key \'' . $key . '\' is missing.');
        }
    }

    public function testTranslationOfOpusTitleAbstractFields() {
        $model = new Opus_TitleAbstract();

        $fieldNames = $model->describe();

        foreach($fieldNames as $name) {
            $key = $this->helper->getKeyForField('Opus_TitleAbstract', $name);
            $this->assertTrue($this->translate->isTranslated($key),
                    'Translation key \'' . $key . '\' is missing.');
        }
    }

    public function testTranslationOfDocumentPersonFields() {
        $model = new Opus_Model_Dependent_Link_DocumentPerson;
        $target = new Opus_Person;
        $model->setModel($target);

        $fieldNames = $model->describe();

        foreach($fieldNames as $name) {
            $key = $this->helper->getKeyForField('Opus_Person', $name);
            $this->assertTrue($this->translate->isTranslated($key),
                    'Translation key \'' . $key . '\' is missing.');
        }
    }

    public function testTranslationOfOpusSubjectFields() {
        $model = new Opus_Subject();

        $fieldNames = $model->describe();

        foreach($fieldNames as $name) {
            $key = $this->helper->getKeyForField('Opus_Subject', $name);
            $this->assertTrue($this->translate->isTranslated($key),
                    'Translation key \'' . $key . '\' is missing.');
        }
    }

    public function testTranslationOfOpusPatentFields() {
        $model = new Opus_Patent();

        $fieldNames = $model->describe();

        foreach($fieldNames as $name) {
            $key = $this->helper->getKeyForField('Opus_Patent', $name);
            $this->assertTrue($this->translate->isTranslated($key),
                    'Translation key \'' . $key . '\' is missing.');
        }
    }

    public function testTranslationOfOpusNoteFields() {
        $model = new Opus_Note();

        $fieldNames = $model->describe();

        foreach($fieldNames as $name) {
            $key = $this->helper->getKeyForField('Opus_Note', $name);
            $this->assertTrue($this->translate->isTranslated($key),
                    'Translation key \'' . $key . '\' is missing.');
        }
    }

    public function testTranslationOfOpusEnrichmentFields() {
        $model = new Opus_Enrichment();

        $fieldNames = $model->describe();

        foreach($fieldNames as $name) {
            $key = $this->helper->getKeyForField('Opus_Enrichment', $name);
            $this->assertTrue($this->translate->isTranslated($key),
                    'Translation key \'' . $key . '\' is missing.');
        }
    }

    public function testTranslationOfLanguages() {
        $languages = Opus_Language::getAll();

        foreach ($languages as $language) {
            $key = $language->getPart2T();
            $this->assertTrue($this->translate->isTranslated($key), $key);
        }
    }

}
