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
 * @category    Application Unit Test
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Account;
use Opus\UserRole;

class SeriesAdminTest extends ControllerTestCase
{

    protected $configModifiable = true;

    protected $additionalResources = ['database', 'translation', 'view', 'navigation', 'mainMenu'];

    private $userName = 'seriesadminuser';

    private $roleName = 'test-seriesadmin';

    public function setUp()
    {
        parent::setUp();
        $this->enableSecurity();

        $userRole = new UserRole();
        $userRole->setName($this->roleName);
        $userRole->appendAccessModule('admin');
        $userRole->appendAccessModule('resource_series');
        $userRole->store();

        $user = new Account();
        $user->setLogin($this->userName);
        $user->setPassword('seriesadminpwd');
        $user->addRole($userRole);
        $user->store();

        $this->loginUser($this->userName, 'seriesadminpwd');
    }

    public function tearDown()
    {
        $this->logoutUser();
        $this->restoreSecuritySetting();

        $user = Account::fetchAccountByLogin($this->userName);
        $user->delete();

        $userRole = UserRole::fetchByName($this->roleName);
        $userRole->delete();

        parent::tearDown();
    }


    /**
     * Regression Test für OPUSVIER-3306.
     */
    public function testAccessEditSeries()
    {
        $this->useEnglish();
        $this->dispatch('/admin/series/edit/id/4');
        $this->assertResponseCode(200);

        $this->assertQueryContentContains('//html/head/title', 'Edit Series');
        $this->assertXPath("//input[@type = 'hidden' and @value = '4']");
    }
}
