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

class Admin_Model_DocumentEditSessionTest extends ControllerTestCase
{
    public function testCreateModel()
    {
        $model = new Admin_Model_DocumentEditSession(146);
        $this->assertEquals(146, $model->getDocumentId());
    }

    public function testCreateModelWithBadId()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('mit document ID \'-1\' aufgerufen');
        new Admin_Model_DocumentEditSession(-1);
    }

    public function testAddPerson()
    {
        $model = new Admin_Model_DocumentEditSession(146);

        $props = [
            'person'  => 310,
            'role'    => 'author',
            'order'   => 2,
            'contact' => 1,
        ];

        $this->assertEquals(0, $model->getPersonCount());

        $model->addPerson($props);

        $this->assertEquals(1, $model->getPersonCount());
    }

    public function testRetrievePersons()
    {
        $model = new Admin_Model_DocumentEditSession(146);

        $this->assertEmpty($model->retrievePersons());

        $props = [
            'person'  => 310,
            'role'    => 'author',
            'order'   => 2,
            'contact' => 1,
        ];

        $model->addPerson($props);

        $this->assertCount(1, $model->retrievePersons());

        // retrievePersons removes variable from session
        $this->assertEmpty($model->retrievePersons());
    }

    public function testStoreRetrievePost()
    {
        $model = new Admin_Model_DocumentEditSession(146);

        $post = [
            'key1'     => 'value1',
            'key2'     => 'value2',
            'subform1' => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
        ];

        $model->storePost($post);

        $this->assertEquals($post, $model->retrievePost());

        // retrievePost removes variable from session
        $this->assertNull($model->retrievePost());
    }

    public function testStoreRetrievePostWithName()
    {
        $model = new Admin_Model_DocumentEditSession(146);

        $post = [
            'key1'     => 'value1',
            'key2'     => 'value2',
            'subform1' => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
        ];

        $model->storePost($post, 'files');

        $this->assertEquals($post, $model->retrievePost('files'));
    }

    public function testGetSessionNamespace()
    {
        $model = new Admin_Model_DocumentEditSession(146);

        $namespace = $model->getSessionNamespace();

        $this->assertNotNull($namespace);

        $this->assertInstanceOf('Zend_Session_Namespace', $namespace);

        // zweimal aufrufen; beim ersten Mal ist die interne Variable noch nicht gesetzt
        $namespace2 = $model->getSessionNamespace();

        $this->assertNotNull($namespace2);
        $this->assertEquals($namespace, $namespace2);
    }

    public function testGetDocumentSessionNamespace()
    {
        $model = new Admin_Model_DocumentEditSession(146);

        $namespace = $model->getDocumentSessionNamespace();

        $this->assertNotNull($namespace);
        $this->assertInstanceOf('Zend_Session_Namespace', $namespace);

        $namespace2 = $model->getDocumentSessionNamespace();

        $this->assertNotNull($namespace2);
        $this->assertEquals($namespace, $namespace2);
    }

    public function testEditTwoDocuments()
    {
        $model1 = new Admin_Model_DocumentEditSession(146);
        $model2 = new Admin_Model_DocumentEditSession(100);

        $namespace1 = $model1->getDocumentSessionNamespace();
        $namespace2 = $model2->getDocumentSessionNamespace();

        $this->assertNotEquals($namespace1, $namespace2);

        $model1->addPerson(['person' => 310]);

        $this->assertEquals(1, $model1->getPersonCount());
        $this->assertEquals(0, $model2->getPersonCount());
    }
}
