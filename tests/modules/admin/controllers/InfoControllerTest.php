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

/**
 * @covers Admin_InfoController
 */
class Admin_InfoControllerTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'all';

    public function testIndexDisplayVersion()
    {
        $config = $this->getConfig();
        $this->dispatch('admin/info');
        $this->assertResponseCode(200);
        $this->assertQuery('//div[@class="opus-version-info"]');
        $this->assertQueryContentContains("//div[@class='opus-version-info']", $config->version);
        $this->validateXHTML();
    }

    /**
     * Test für OPUSVIER-1386.
     */
    public function testVersionWithOldVersion()
    {
        $this->useEnglish();

        // set version that would otherwise be retrieved from server
        $versionHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('version');
        $versionHelper->setVersion('4.6');

        $config          = $this->getConfig();
        $oldVersion      = $config->version;
        $config->version = '4.5-TEST';
        $this->dispatch('admin/info/update');
        $config->version = $oldVersion;
        $this->validateXHTML();
        $this->assertQueryContentContains('//dt', "Latest OPUS version");
        $this->assertQueryContentContains('//dd', "4.5-TEST");
        $this->assertQueryContentContains('//a', "Get the latest version here.");
        $this->assertQueryContentContains('//div', "Your OPUS version is not up to date.");
    }

    /**
     * Test für OPUSVIER-1386.
     */
    public function testVersionWithCurrentVersion()
    {
        $this->useEnglish();
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('version');
        $helper->setVersion($this->getConfig()->version);

        $this->dispatch('admin/info/update');
        $this->assertQueryContentContains('//div', 'Your OPUS version is up to date.');
    }
}
