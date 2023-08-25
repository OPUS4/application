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
 * @copyright   Copyright (c) 2023, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Oai_Model_ServerFactoryTest extends ControllerTestCase
{
    private $serverFactory;

    public function setUp(): void
    {
        parent::setUp();

        $config = new Zend_Config(
            [
                'workspacePath' => '/vagrant/tests/workspace',
                'mail' => [
                    'opus' => [
                        'address' => 'opus4ci@example.org'
                    ],
                ],
                'oai' => [
                    'max' => [
                        'listrecords' => 10,
                        'listidentifiers' => 10,
                    ],
                    'format' => [
                        'default' => [
                            'class' => 'DefaultServerClass'
                        ],
                        'epicur' => [
                            'class'    => 'UnknownEpicurClass',
                            'xsltFile' => 'epicurFile.xslt',
                        ],
                        'oai_dc' => [
                            'class' => 'OaiDcServer',
                        ],
                        'oai_pp' => null,
                    ],
                ]
            ]
        );

        $this->serverFactory = new Oai_Model_ServerFactory();
        $this->serverFactory->setConfig($config);
    }

    public function testCreate()
    {
        $server         = $this->serverFactory->create();
        $expectedServer = new Oai_Model_BaseServer();
        $expectedServer->setMaxListIdentifiers(10);
        $expectedServer->setMaxListRecords(10);
        $expectedServer->setResumptionTokenPath('/vagrant/tests/workspace/tmp/resumption');
        $expectedServer->setEmailContact('opus4ci@example.org');
        $this->assertEquals(Oai_Model_BaseServer::class, get_class($server));
        $this->assertEquals($expectedServer->getOptions(), $server->getOptions());

        $server         = $this->serverFactory->create('epicur');
        $expectedServer = new Oai_Model_BaseServer();
        $expectedServer->setXsltFile('epicurFile.xslt');
        $expectedServer->setMaxListIdentifiers(10);
        $expectedServer->setMaxListRecords(10);
        $expectedServer->setResumptionTokenPath('/vagrant/tests/workspace/tmp/resumption');
        $expectedServer->setEmailContact('opus4ci@example.org');
        $this->assertEquals(Oai_Model_BaseServer::class, get_class($server));
        $this->assertEquals($expectedServer->getOptions(), $server->getOptions());

        $server         = $this->serverFactory->create('oai_dc');
        $expectedServer = new OaiDcServer();
        $expectedServer->setMaxListIdentifiers(10);
        $expectedServer->setMaxListRecords(10);
        $expectedServer->setResumptionTokenPath('/vagrant/tests/workspace/tmp/resumption');
        $expectedServer->setEmailContact('opus4ci@example.org');
        $this->assertEquals(OaiDcServer::class, get_class($server));
        $this->assertEquals($expectedServer->getOptions(), $server->getOptions());

        $server         = $this->serverFactory->create('oai_pp');
        $expectedServer = new Oai_Model_BaseServer();
        $expectedServer->setMaxListIdentifiers(10);
        $expectedServer->setMaxListRecords(10);
        $expectedServer->setResumptionTokenPath('/vagrant/tests/workspace/tmp/resumption');
        $expectedServer->setEmailContact('opus4ci@example.org');
        $this->assertEquals(Oai_Model_BaseServer::class, get_class($server));
        $this->assertEquals($expectedServer->getOptions(), $server->getOptions());
    }

    public function testGetFormatClassName()
    {
        $metadDataPrefix = 'epicur';
        $serverClass     = $this->serverFactory->getFormatClassName($metadDataPrefix);
        $this->assertEquals('UnknownEpicurClass', $serverClass);

        $metadDataPrefix = 'EpiCur';
        $serverClass     = $this->serverFactory->getFormatClassName($metadDataPrefix);
        $this->assertEquals('UnknownEpicurClass', $serverClass);

        $metadDataPrefix = 'unknown';
        $serverClass     = $this->serverFactory->getFormatClassName($metadDataPrefix);
        $this->assertEquals('DefaultServerClass', $serverClass);
    }

    public function testGetFormatOptions()
    {
        $expectedOptions = [
            'xsltFile' => 'epicurFile.xslt',
            'maxListIdentifiers' => 10,
            'maxListRecords' => 10,
            'resumptionTokenPath' => '/vagrant/tests/workspace/tmp/resumption',
            'emailContact' => 'opus4ci@example.org',
            'class' => 'UnknownEpicurClass',
        ];

        $metadDataPrefix = 'epicur';
        $options         = $this->serverFactory->getFormatOptions($metadDataPrefix);
        $this->assertEquals($expectedOptions, $options);

        $metadDataPrefix = 'EpiCur';
        $options         = $this->serverFactory->getFormatOptions($metadDataPrefix);
        $this->assertEquals($expectedOptions, $options);

        $metadDataPrefix = 'unknown';
        $options         = $this->serverFactory->getFormatOptions($metadDataPrefix);
        unset($expectedOptions['xsltFile']);
        unset($expectedOptions['class']);
        $this->assertEquals($expectedOptions, $options);
    }
}
