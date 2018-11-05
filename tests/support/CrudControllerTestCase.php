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
 * @category    Application Unit Test
 * @package     Test_Support
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

abstract class CrudControllerTestCase extends ControllerTestCase {

    private $oldModelIds;

    private $module = 'admin';

    private $controller = null;

    private $controllerPath;

    protected $createsModels;

    public function setModule($value) {
        $this->module = $value;
    }

    public function setController($value) {
        $this->controller = $value;
    }

    public function setUp() {
        $this->controllerPath = '/' . $this->module . '/' . $this->controller;
        parent::setUp();

        $this->createsModels = false;

        $this->licences = array();

        $models = $this->getModels();

        foreach ($models as $model) {
            $this->oldModelIds[] = $model->getId();
        }
    }

    public function getControllerPath() {
        return $this->controllerPath;
    }

    public function tearDown() {
        if ($this->createsModels) {
            $this->deleteNewModels();
        }
        parent::tearDown();
    }

    private function deleteNewModels() {
        $models = $this->getModels();
        if (is_array($models))
        {
            foreach ($models as $model)
            {
                if (empty($this->oldModelIds) || !in_array($model->getId(), $this->oldModelIds))
                {
                    $model->delete();
                }
            }
        }
    }

    abstract function getModels();

    public function testIndexAction() {
        $this->dispatch($this->controllerPath);
        $this->assertResponseCode(200);
        $this->assertController($this->controller);
        $this->assertAction('index');

        $models = $this->getModels();;

        $this->assertQuery('a.add', 'Kein Add Button gefunden.');
        if (count($models) > 0)
        {
            $this->assertQuery('td.edit', count($models));
        }

        foreach ($models as $model) {
            $this->assertQuery('th', $model->getDisplayName());
        }
    }

    public function testShowActionBadId() {
        $this->dispatch($this->controllerPath . '/show/id/bla');
        $this->assertRedirectTo($this->controllerPath);
        $this->verifyFlashMessage('controller_crud_invalid_id');
    }

    public function testShowActionBadUnknownId() {
        $this->dispatch($this->controllerPath . '/show/id/1000');
        $this->assertRedirectTo($this->controllerPath);
        $this->verifyFlashMessage('controller_crud_invalid_id');
    }

    public function testShowActionNoId() {
        $this->dispatch($this->controllerPath . '/show');
        $this->assertRedirectTo($this->controllerPath);
        $this->verifyFlashMessage('controller_crud_invalid_id');
    }

    /**
     * Tests 'new' action.
     */
    public function testNewActionShowForm() {
        $this->dispatch($this->controllerPath . '/new');
        $this->assertResponseCode(200);
        $this->assertController($this->controller);
        $this->assertAction('new');

        $this->assertQuery('li.save-element');
        $this->assertQuery('li.cancel-element');
        $this->assertQueryCount('input#Id', 1);
    }

    public function testEditActionBadId() {
        $this->dispatch($this->controllerPath . '/edit/id/notanid');
        $this->assertRedirectTo($this->controllerPath);
        $this->verifyFlashMessage('controller_crud_invalid_id');
    }

    public function testEditActionUnknownId() {
        $this->dispatch($this->controllerPath . '/edit/id/1000');
        $this->assertRedirectTo($this->controllerPath);
        $this->verifyFlashMessage('controller_crud_invalid_id');
    }

    public function testEditActionNoId() {
        $this->dispatch($this->controllerPath . '/edit');
        $this->assertRedirectTo($this->controllerPath);
        $this->verifyFlashMessage('controller_crud_invalid_id');
    }

    public function testBreadcrumbsDefined() {
        $this->verifyBreadcrumbDefined($this->controllerPath . '/index');
        $this->verifyBreadcrumbDefined($this->controllerPath . '/show');
        $this->verifyBreadcrumbDefined($this->controllerPath . '/new');
        $this->verifyBreadcrumbDefined($this->controllerPath . '/edit');
        $this->verifyBreadcrumbDefined($this->controllerPath . '/delete');
    }

    public function testDeleteActionBadId() {
        $this->dispatch($this->controllerPath . '/delete/id/notanid');
        $this->assertRedirectTo($this->controllerPath);
        $this->verifyFlashMessage('controller_crud_invalid_id');
    }

    public function testDeleteActionUnknownId() {
        $this->dispatch($this->controllerPath . '/delete/id/1000');
        $this->assertRedirectTo($this->controllerPath);
        $this->verifyFlashMessage('controller_crud_invalid_id');
    }

    public function testDeleteActionNoId() {
        $this->dispatch($this->controllerPath . '/delete');
        $this->assertRedirectTo($this->controllerPath);
        $this->verifyFlashMessage('controller_crud_invalid_id');
    }

    abstract function createNewModel();

    abstract function getModel($identifier);

    public function testDeleteActionYes() {
        $this->createsModels = true;

        $modelId = $this->createNewModel();

        $this->getRequest()->setMethod('POST')->setPost(array(
            'Id' => $modelId,
            'ConfirmYes' => 'Ja'
        ));

        $this->dispatch($this->controllerPath . '/delete');

        try {
            $this->getModel($modelId);
        }
        catch (Opus_Model_NotFoundException $omnfe) {
            // alles gut, Modell wurde geloescht
        }

        $this->assertRedirectTo($this->controllerPath);
        $this->verifyFlashMessage('controller_crud_delete_success', self::MESSAGE_LEVEL_NOTICE);
    }

    public function testDeleteActionNo() {
        $this->createsModels = true;
        $this->useEnglish();

        $modelId = $this->createNewModel();

        $this->getRequest()->setMethod('POST')->setPost(array(
            'Id' => $modelId,
            'ConfirmNo' => 'Nein'
        ));

        $this->dispatch($this->controllerPath . '/delete/id/' . $modelId);

        $this->assertNotNull($this->getModel($modelId)); // Lizenz nicht geloescht, alles gut

        $this->assertRedirectTo($this->controllerPath);
    }

}
