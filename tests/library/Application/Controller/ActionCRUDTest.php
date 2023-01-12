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

use Opus\Common\Licence;
use Opus\Common\LicenceInterface;
use Opus\Document;
use Opus\EnrichmentKey;

/**
 * Erstellt und löscht Lizenzen.
 */
class Application_Controller_ActionCRUDTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'view', 'mainMenu', 'navigation', 'translation'];

    /** @var Application_Controller_ActionCRUD */
    private $controller;

    /** @var int[] */
    private $licenceIds;

    public function setUp(): void
    {
        parent::setUp();

        $this->controller = $this->getController();
        $this->controller->setFormClass('Admin_Form_Licence');

        $licences = Licence::getAll();

        $this->licenceIds = [];

        foreach ($licences as $licence) {
            $this->licenceIds[] = $licence->getId();
        }
    }

    public function tearDown(): void
    {
        $licences = Licence::getAll();

        if (count($this->licenceIds) < count($licences)) {
            foreach ($licences as $licence) {
                if (! in_array($licence->getId(), $this->licenceIds)) {
                    $licence->delete();
                }
            }
        }

        parent::tearDown();
    }

    /**
     * @param array $messages
     */
    private function verifyMessages($messages)
    {
        $this->assertArrayHasKey(Application_Controller_ActionCRUD::SAVE_SUCCESS, $messages);
        $this->assertArrayHasKey(Application_Controller_ActionCRUD::SAVE_FAILURE, $messages);
        $this->assertArrayHasKey(Application_Controller_ActionCRUD::DELETE_SUCCESS, $messages);
        $this->assertArrayHasKey(Application_Controller_ActionCRUD::DELETE_FAILURE, $messages);
        $this->assertArrayHasKey(Application_Controller_ActionCRUD::INVALID_ID, $messages);
    }

    /**
     * @return Application_Controller_ActionCRUD
     */
    private function getController()
    {
        return new Application_Controller_ActionCRUD($this->getRequest(), $this->getResponse());
    }

    public function testSetGetFormClass()
    {
        $controller = $this->getController();

        $this->assertNull($controller->getFormClass());

        $controller->setFormClass('Admin_Form_Licence');

        $this->assertEquals('Admin_Form_Licence', $controller->getFormClass());
    }

    public function testSetFormClassBadClass()
    {
        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('not instance of Application_Form_IModel');
        $this->controller->setFormClass(Document::class);
    }

    public function testIsClassSupportedTrue()
    {
        $this->assertTrue($this->controller->isClassSupported('Admin_Form_Licence'));
    }

    public function testIsClassSupportedFalse()
    {
        $this->assertFalse($this->controller->isClassSupported('Admin_Form_Document_Licences'));
    }

    public function testGetAllModels()
    {
        $licences = Licence::getAll();

        $models = $this->controller->getAllModels();

        $this->assertEquals(count($licences), count($models));
    }

    public function testGetModel()
    {
        $model = $this->controller->getModel(2);

        $this->assertNotNull($model);
        $this->assertInstanceOf(LicenceInterface::class, $model);
        $this->assertEquals(2, $model->getId());
    }

    public function testGetModelUnkownId()
    {
        $model = $this->controller->getModel(1000);

        $this->assertNull($model);
    }

    public function testGetModelBadId()
    {
        $model = $this->controller->getModel('notAnId');

        $this->assertNull($model);
    }

    public function testGetModelWithStringId()
    {
        $this->controller->setVerifyModelIdIsNumeric(false);
        $this->controller->setFormClass('Admin_Form_EnrichmentKey');

        $model = $this->controller->getModel('City');

        $this->assertNotNull($model);
        $this->assertInstanceOf(EnrichmentKey::class, $model);
        $this->assertEquals('City', $model->getName());
    }

    public function testGetModelEmptyId()
    {
        $model = $this->controller->getModel('');

        $this->assertNull($model);
    }

    public function testGetNewModel()
    {
        $model = $this->controller->getNewModel();

        $this->assertNotNull($model);
        $this->assertInstanceOf(LicenceInterface::class, $model);
        $this->assertNull($model->getId());
    }

    public function testGetModelForm()
    {
        $form = $this->controller->getModelForm();

        $this->assertNotNull($form);
        $this->assertInstanceOf('Admin_Form_Licence', $form);
    }

    public function testGetNewModelForm()
    {
        $form = $this->controller->getNewModelForm();

        $this->assertNotNull($form);
        $this->assertInstanceOf('Admin_Form_Licence', $form);
        $this->assertNull($form->getElement(Application_Form_Model_Abstract::ELEMENT_MODEL_ID)->getValue());
    }

    public function testGetEditModelForm()
    {
        $model = Licence::get(2);

        $form = $this->controller->getEditModelForm($model);

        $this->assertNotNull($form);
        $this->assertInstanceOf('Admin_Form_Licence', $form);
        $this->assertEquals(2, $form->getElement(Application_Form_Model_Abstract::ELEMENT_MODEL_ID)->getValue());
    }

    public function testGetMessages()
    {
        $messages = $this->controller->getMessages();

        $this->assertEquals(7, count($messages));
        $this->verifyMessages($messages);
    }

    public function testSetMessages()
    {
        $this->controller->setMessages([
            'saveSuccess' => 'success',
            'saveFailure' => 'failure',
        ]);
    }

    public function testLoadDefaultMessages()
    {
        $messages = $this->controller->getMessages();

        $this->assertEquals(7, count($messages));
        $this->verifyMessages($messages);
    }

    public function testGetConfirmationForm()
    {
        $model = Licence::get(2);
        $form  = $this->controller->getConfirmationForm($model);
        $this->assertNotNull($form);
        $this->assertInstanceOf('Application_Form_Confirmation', $form);
        $this->assertEquals(2, $form->getModelId());
    }

    public function testGetConfirmationFormNull()
    {
        $form = $this->controller->getConfirmationForm(null);
        $this->assertNotNull($form);
        $this->assertInstanceOf('Application_Form_Confirmation', $form);
    }

    public function testHandlePostCancel()
    {
        $result = $this->controller->handleModelPost([
            'Cancel' => 'Abbrechen',
        ]);

        $this->assertNotNull($result);
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    public function testHandlePostSave()
    {
        $result = $this->controller->handleModelPost([
            'Save'        => 'Abspeichern',
            'NameLong'    => 'New Test Licence',
            'Language'    => 'deu',
            'LinkLicence' => 'www.example.org/licence',
        ]);

        $this->assertNotNull($result);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('action', $result);
        $this->assertEquals('show', $result['action']);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals(Application_Controller_ActionCRUD::SAVE_SUCCESS, $result['message']);
        $this->assertArrayHasKey('params', $result);

        $params = $result['params'];
        $this->assertArrayHasKey('id', $params);

        $licenceId = $params['id'];

        $licence = Licence::get($licenceId);
        $licence->delete();
    }

    public function testHandlePostSaveShowDisabled()
    {
        $this->controller->setShowActionEnabled(false);

        $this->assertFalse($this->controller->getShowActionEnabled());

        $result = $this->controller->handleModelPost([
            'Save'        => 'Abspeichern',
            'NameLong'    => 'New Test Licence',
            'Language'    => 'deu',
            'LinkLicence' => 'www.example.org/licence',
        ]);

        $this->assertNotNull($result);
        $this->assertInternalType('array', $result);
        $this->assertArrayNotHasKey('action', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals(Application_Controller_ActionCRUD::SAVE_SUCCESS, $result['message']);
    }

    public function testHandlePostSaveInvalid()
    {
        $result = $this->controller->handleModelPost([
            'Save'        => 'Abspeichern',
            'NameLong'    => '', // is required
            'Language'    => 'abc',
            'LinkLicence' => 'www.example.org/licence',
        ]);

        $this->assertNotNull($result);
        $this->assertInstanceOf(Application_Form_ModelFormInterface::class, $result);

        $this->assertEquals('abc', $result->getElement('Language')->getValue());
        $this->assertEquals('www.example.org/licence', $result->getElement('LinkLicence')->getValue());
    }

    public function testHandlePostSaveInvalidId()
    {
        $result = $this->controller->handleModelPost([
            'Save'        => 'Abspeichern',
            'Id'          => 1000,
            'NameLong'    => 'Test Licence',
            'Language'    => 'deu',
            'LinkLicence' => 'www.example.org/licence',
        ]);

        $this->assertNotNull($result);
        $this->assertInternalType('array', $result);
        $this->assertEquals(1, count($result));
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals(Application_Controller_ActionCRUD::INVALID_ID, $result['message']);
    }

    public function testHandlePostNoSaveOrCancel()
    {
        $result = $this->controller->handleModelPost([]);

        $this->assertNotNull($result);
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    public function testHandlePostGetPostIfParamNull()
    {
        $this->getRequest()->setMethod('POST')->setPost([
            'Save'        => 'Abspeichern',
            'Id'          => 1000,
            'NameLong'    => 'Test Licence',
            'Language'    => 'deu',
            'LinkLicence' => 'www.example.org/licence',
        ]);

        $result = $this->controller->handleModelPost();

        $this->assertNotNull($result);
        $this->assertInternalType('array', $result);
        $this->assertEquals(1, count($result));
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals(Application_Controller_ActionCRUD::INVALID_ID, $result['message']);
    }

    public function testHandleConfirmationPostInvalid()
    {
        $result = $this->controller->handleConfirmationPost([]);

        $this->assertNotNull($result);
        $this->assertInternalType('array', $result);
        $this->assertEquals(1, count($result));
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals(Application_Controller_ActionCRUD::INVALID_ID, $result['message']);
    }

    public function testHandleConfirmationPostNo()
    {
        $result = $this->controller->handleConfirmationPost([
            'Id'        => '1',
            'ConfirmNo' => 'Nein',
        ]);

        $this->assertNotNull($result);
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    public function testHandleConfirmationPostInvalidId()
    {
        $result = $this->controller->handleConfirmationPost([
            'Id'         => '1000',
            'ConfirmYes' => 'Ja',
        ]);

        $this->assertNotNull($result);
        $this->assertInternalType('array', $result);
        $this->assertEquals(1, count($result));
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals(Application_Controller_ActionCRUD::INVALID_ID, $result['message']);
    }

    public function testHandleConfirmationPostNoParamNull()
    {
        $this->getRequest()->setMethod('POST')->setPost([
            'Id'        => '1',
            'ConfirmNo' => 'Nein',
        ]);

        $result = $this->controller->handleConfirmationPost();

        $this->assertNotNull($result);
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    public function testHandleConfirmationPostYes()
    {
        $licence = Licence::new();

        $licence->setNameLong(__METHOD__);
        $licence->setLanguage('deu');
        $licence->setLinkLicence('www.example.org/licence');

        $licenceId = $licence->store();

        $result = $this->controller->handleConfirmationPost([
            'Id'         => $licenceId,
            'ConfirmYes' => 'Ja',
        ]);

        $this->assertNotNull($result);
        $this->assertInternalType('array', $result);
        $this->assertEquals(1, count($result));
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals(Application_Controller_ActionCRUD::DELETE_SUCCESS, $result['message']);
    }

    public function testMessagesTranslated()
    {
        $messages = $this->controller->getMessages();

        $translate = Application_Translate::getInstance();

        foreach ($messages as $message) {
            if (is_array($message)) {
                $this->assertArrayHasKey('failure', $message);
                $key = $message['failure'];
            } else {
                $key = $message;
            }

            $this->assertTrue($translate->isTranslated($key), "Message '$key' hat keine Uebersetzung.");
        }
    }

    public function testSetGetFunctionNameForGettingModels()
    {
        $this->assertEquals('getAll', $this->controller->getFunctionNameForGettingModels());

        $this->controller->setFormClass(Admin_Form_Series::class);

        $series = $this->controller->getAllModels();

        $this->controller->setFunctionNameForGettingModels('getAllSortedBySortKey');

        $sortedSeries = $this->controller->getAllModels();

        $this->assertEquals(count($series), count($sortedSeries));

        // Prüfen, ob die sortierende Funktion verwendet wurde
        $lastValue = null;
        foreach ($sortedSeries as $series) {
            $sortOrder = $series->getSortOrder();
            $this->assertTrue($lastValue = null || $lastValue <= $sortOrder, 'Series are not properly sorted.');
            $lastValue = $sortOrder;
        }

        $this->assertEquals('getAllSortedBySortKey', $this->controller->getFunctionNameForGettingModels());

        $this->controller->setFunctionNameForGettingModels(null);

        $this->assertEquals('getAll', $this->controller->getFunctionNameForGettingModels());
    }

    public function testGetIndexForm()
    {
        $form = $this->controller->getIndexForm();

        $this->assertInstanceOf(Application_Form_Model_Table::class, $form);
    }
}
