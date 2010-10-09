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
 * @package     Tests
 * @author      Sascha Szott <szott@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Basic unit tests for the collections controller in admin module.
 */
class Admin_CollectionControllerTest extends ControllerTestCase {

    /**
     * Test list all collection roots.
     */
    public function testIndexAction() {
        $this->dispatch('/admin/collection');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('collection');
        $this->assertAction('index');
    }

    /**
     * Test show first level of collection.
     */
    public function testShowAction() {
        $this->markTestIncomplete('todo');
        $this->dispatch('/admin/collection/show/id/2');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('collection');
        $this->assertAction('show');
    }

    /**
     * Test opening collection role for editing.
     */
    public function testEditRoleAction() {
        $this->dispatch('/admin/collection/editrole/roleid/1');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('collection');
        $this->assertAction('editrole');
    }

    /**
     * Test opening collection for editing.
     */
    public function testEditAction() {
        $this->dispatch('/admin/collection/edit/id/3');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('collection');
        $this->assertAction('edit');
    }
}
?>