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
 */

/**
 * Unit Tests fuer abstrakte Basisklasse fuer Modelle.
 *
 * @category    Application Unit Test
 * @package     Application_Model
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
class Application_Model_AbstractTest extends ControllerTestCase
{

    private $_model;

    public function setUp()
    {
        parent::setUp();
        $this->_model = $this->getModel();
    }

    private function getModel()
    {
        return $this->getMockForAbstractClass('Application_Model_Abstract');
    }

    public function testGetLogger()
    {
        $logger = $this->_model->getLogger();

        $this->assertNotNull($logger);
        $this->assertInstanceOf('Zend_Log', $logger);
    }

    public function testSetLogger()
    {
        $logger = new MockLogger();

        $this->_model->setLogger($logger);

        $this->assertNotNull($this->_model->getLogger());
        $this->assertInstanceOf('MockLogger', $this->_model->getLogger());
    }

    public function testGetConfig()
    {
        $config = $this->_model->getConfig();

        $this->assertInstanceOf('Zend_Config', $config);
        $this->assertEquals(Zend_Registry::get('Zend_Config'), $config);
    }

    public function testSetConfig()
    {
        $config = new Zend_Config([]);

        $this->_model->setConfig($config);

        $returnedConfig = $this->_model->getConfig();

        $this->assertInstanceOf('Zend_Config', $returnedConfig);
        $this->assertEquals($config, $returnedConfig);
    }
}
