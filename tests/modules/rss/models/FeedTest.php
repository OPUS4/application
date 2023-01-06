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

class Rss_Model_FeedTest extends ControllerTestCase
{
    /** @var bool */
    protected $configModifiable = true;

    /** @var string[] */
    protected $additionalResources = ['view'];

    /** @var Rss_Model_Feed */
    private $model;

    public function setUp(): void
    {
        parent::setUp();

        $view = $this->getView();

        $this->model = new Rss_Model_Feed($view);
    }

    public function testGetTitle()
    {
        $view = $this->getView();
        Zend_Controller_Front::getInstance()->setBaseUrl('/opus4test');
        $model = new Rss_Model_Feed($view);

        $this->assertEquals('http:///opus4test', $model->getTitle());

        $this->adjustConfiguration([
            'rss' => ['default' => ['feedTitle' => 'OPUS 4 Test']],
        ]);

        $model->setConfig(null); // reset local reference to configuration

        $this->assertEquals('OPUS 4 Test', $model->getTitle());
    }

    public function testGetTitleWithName()
    {
        $this->adjustConfiguration([
            'rss' => ['default' => ['feedTitle' => '%1$s']],
        ]);
        $this->assertEquals('OPUS 4', $this->model->getTitle());
    }

    public function testGetTitleWithFullUrl()
    {
        $view = $this->getView();
        Zend_Controller_Front::getInstance()->setBaseUrl('/opus4test');
        $model = new Rss_Model_Feed($view);

        $this->adjustConfiguration([
            'rss' => ['default' => ['feedTitle' => '%4$s']],
        ]);
        $this->assertEquals('http:///opus4test', $this->model->getTitle());
    }

    public function testGetTitleWithBaseUrl()
    {
        $view = $this->getView();
        Zend_Controller_Front::getInstance()->setBaseUrl('/opus4test');
        $model = new Rss_Model_Feed($view);

        $this->adjustConfiguration([
            'rss' => ['default' => ['feedTitle' => '%3$s']],
        ]);
        $this->assertEquals('opus4test', $model->getTitle());
    }

    public function testGetTitleWithHost()
    {
        $view = $this->getView();
        Zend_Controller_Front::getInstance()->setBaseUrl('/opus4test');
        $view->getHelper('ServerUrl')->setHost('testhost');
        $model = new Rss_Model_Feed($view);

        $this->adjustConfiguration([
            'rss' => ['default' => ['feedTitle' => '%2$s']],
        ]);
        $this->assertEquals('testhost', $model->getTitle());
    }

    public function testGetDescription()
    {
        $this->assertEquals('OPUS documents', $this->model->getDescription());

        $this->adjustConfiguration([
            'rss' => ['default' => ['feedDescription' => 'Test description.']],
        ]);

        $this->model->setConfig(null); // reset local reference to configuration

        $this->assertEquals('Test description.', $this->model->getDescription());
    }
}
