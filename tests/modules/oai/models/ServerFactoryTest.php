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
    /**
     * @return array
     */
    protected function getConfigurationArray()
    {
        return [
            'workspacePath' => '/vagrant/tests/workspace',
            'mail'          => [
                'opus' => [
                    'address' => 'opus4ci@example.org',
                ],
            ],
            'oai'           => [
                'max'    => [
                    'listrecords'     => 10,
                    'listidentifiers' => 10,
                ],
                'format' => [
                    'default' => [
                        'class' => DefaultOaiServer::class,
                    ],
                    'oai_dc'  => [
                        'class'    => OaiDcServer::class,
                        'xsltFile' => 'oaiFile.xslt',
                    ],
                    'oai_pp'  => null,
                ],
            ],
        ];
    }

    /**
     * @param array|null $configurationArray
     * @return Oai_Model_ServerFactory
     */
    protected function createServerFactory($configurationArray = null)
    {
        if ($configurationArray === null) {
            $config = new Zend_Config($this->getConfigurationArray());
        } else {
            $config = new Zend_Config($configurationArray);
        }

        $serverFactory = new Oai_Model_ServerFactory();
        $serverFactory->setConfig($config);
        return $serverFactory;
    }

    public function testCreateWithValidMetadataPrefix()
    {
        $serverFactory = $this->createServerFactory();
        $server        = $serverFactory->create('oai_dc');

        $expectedOptions = [
            'maxListIdentifiers'  => 10,
            'maxListRecords'      => 10,
            'resumptionTokenPath' => '/vagrant/tests/workspace/tmp/resumption',
            'emailContact'        => 'opus4ci@example.org',
            'xsltFile'            => 'oaiFile.xslt',
        ];

        $this->assertEquals(OaiDcServer::class, get_class($server));
        $this->assertEquals($expectedOptions, $server->getOptions(array_keys($expectedOptions)));
    }

    public function testCreateWithNoneExistingFormatServerClass()
    {
        $configArray = $this->getConfigurationArray();

        $configArray['oai']['format']['oai_dc']['class'] = 'UnknownOaiDcServerClass';

        $serverFactory = $this->createServerFactory($configArray);

        $server = $serverFactory->create('oai_dc');

        $expectedOptions = [
            'maxListIdentifiers'  => 10,
            'maxListRecords'      => 10,
            'resumptionTokenPath' => '/vagrant/tests/workspace/tmp/resumption',
            'emailContact'        => 'opus4ci@example.org',
            'xsltFile'            => 'oaiFile.xslt',
        ];

        $this->assertEquals(Oai_Model_BaseServer::class, get_class($server));
        $this->assertEquals($expectedOptions, $server->getOptions(array_keys($expectedOptions)));
    }

    public function testCreateWithNoneExistingDefaultServerClass()
    {
        $configArray = $this->getConfigurationArray();
        unset($configArray['oai']['format']['oai_dc']['class']);
        $configArray['oai']['format']['default']['class'] = 'UnknownDefaultServerClass';

        $serverFactory = $this->createServerFactory($configArray);

        $server = $serverFactory->create('oai_dc');

        $expectedOptions = [
            'maxListIdentifiers'  => 10,
            'maxListRecords'      => 10,
            'resumptionTokenPath' => '/vagrant/tests/workspace/tmp/resumption',
            'emailContact'        => 'opus4ci@example.org',
            'xsltFile'            => 'oaiFile.xslt',
        ];

        $this->assertEquals(Oai_Model_BaseServer::class, get_class($server));
        $this->assertEquals($expectedOptions, $server->getOptions(array_keys($expectedOptions)));
    }

    public function testCreateWithNoPrefix()
    {
        $serverFactory = $this->createServerFactory();
        $server        = $serverFactory->create();

        $expectedOptions = [
            'maxListIdentifiers'  => 10,
            'maxListRecords'      => 10,
            'resumptionTokenPath' => '/vagrant/tests/workspace/tmp/resumption',
            'emailContact'        => 'opus4ci@example.org',
        ];

        $this->assertEquals(DefaultOaiServer::class, get_class($server));
        $this->assertEquals($expectedOptions, $server->getOptions(array_keys($expectedOptions)));
    }

    public function testCreateWithPrefixNotConfigured()
    {
        $serverFactory = $this->createServerFactory();
        $server        = $serverFactory->create('oai_pp');

        $expectedOptions = [
            'maxListIdentifiers'  => 10,
            'maxListRecords'      => 10,
            'resumptionTokenPath' => '/vagrant/tests/workspace/tmp/resumption',
            'emailContact'        => 'opus4ci@example.org',
        ];

        $this->assertEquals(DefaultOaiServer::class, get_class($server));
        $this->assertEquals($expectedOptions, $server->getOptions(array_keys($expectedOptions)));
    }

    public function testGetFormatClassName()
    {
        $serverFactory = $this->createServerFactory();

        $metaDataPrefix = 'oai_dc';
        $options        = $serverFactory->getFormatOptions($metaDataPrefix);
        $this->assertEquals(OaiDcServer::class, $options['class']);

        $metaDataPrefix = 'oai_Dc';
        $options        = $serverFactory->getFormatOptions($metaDataPrefix);
        $this->assertEquals(OaiDcServer::class, $options['class']);
    }

    public function testGetFormatClassNameWithUnknownPrefix()
    {
        $configArray = $this->getConfigurationArray();

        $serverFactory = $this->createServerFactory($configArray);

        $metaDataPrefix = 'unknown';
        $options        = $serverFactory->getFormatOptions($metaDataPrefix);
        $this->assertEquals(DefaultOaiServer::class, $options['class']);
    }

    public function testGetFormatOptions()
    {
        $serverFactory = $this->createServerFactory();

        $expectedOptions = [
            'xsltFile'            => 'oaiFile.xslt',
            'maxListIdentifiers'  => 10,
            'maxListRecords'      => 10,
            'resumptionTokenPath' => '/vagrant/tests/workspace/tmp/resumption',
            'emailContact'        => 'opus4ci@example.org',
            'class'               => OaiDcServer::class,
        ];

        $metaDataPrefix = 'oai_dc';
        $options        = $serverFactory->getFormatOptions($metaDataPrefix);
        $this->assertEquals($expectedOptions, $options);

        $metaDataPrefix = 'oai_Dc';
        $options        = $serverFactory->getFormatOptions($metaDataPrefix);
        $this->assertEquals($expectedOptions, $options);
    }

    public function testGetFormatOptionsWithUnknownMetaDataPrefix()
    {
        $serverFactory = $this->createServerFactory();

        $expectedOptions = [
            'maxListIdentifiers'  => 10,
            'maxListRecords'      => 10,
            'resumptionTokenPath' => '/vagrant/tests/workspace/tmp/resumption',
            'emailContact'        => 'opus4ci@example.org',
            'class'               => DefaultOaiServer::class,
        ];

        $metaDataPrefix = 'unknown';
        $options        = $serverFactory->getFormatOptions($metaDataPrefix);

        $this->assertEquals($expectedOptions, $options);
    }

    public function testConfigurationOverwritesXsltFileValueFromPrefixClass()
    {
        $configArray = $this->getConfigurationArray();

        $configArray['oai']['format']['xmetadissplus'] = [
            'class'    => Oai_Model_Prefix_MarcXml_MarcXmlServer::class,
            'xsltFile' => 'XMetaDissPlus.xslt',
        ];

        $serverFactory = $this->createServerFactory($configArray);

        $server = $serverFactory->create('xmetadissplus');

        $this->assertEquals('XMetaDissPlus.xslt', $server->getXsltFile());
    }

    public function testXsltFileNotConfiguredButSetInPrefixClass()
    {
        $configArray = $this->getConfigurationArray();

        $configArray['oai']['format']['xmetadissplus'] = [
            'class' => Oai_Model_Prefix_MarcXml_MarcXmlServer::class,
        ];

        $serverFactory = $this->createServerFactory($configArray);

        $server = $serverFactory->create('xmetadissplus');

        $this->assertEquals('marc21.xslt', $server->getXsltFile());
    }

    public function testViewHelperConfiguredAsArray()
    {
        $configArray = $this->getConfigurationArray();

        $configArray['oai']['format']['default'] = [
            'viewHelper' => [
                'optionValue',
                'fileUrl',
                'frontdoorUrl',
                'transferUrl',
                'dcmiType',
                'dcType',
                'openAireType',
            ],
        ];

        $serverFactory = $this->createServerFactory($configArray);

        $expectedViewHelpers = [
            'optionValue',
            'fileUrl',
            'frontdoorUrl',
            'transferUrl',
            'dcmiType',
            'dcType',
            'openAireType',
        ];

        $options = $serverFactory->getFormatOptions();

        $this->assertEquals($expectedViewHelpers, $options['viewHelper']);
    }

    public function testViewHelperConfiguredAsCommaSeparatedList()
    {
        $configArray = $this->getConfigurationArray();

        $configArray['oai']['format']['default'] = [
            'viewHelper' => 'optionValue, fileUrl, frontdoorUrl, transferUrl, dcmiType, dcType, openAireType',
        ];

        $serverFactory = $this->createServerFactory($configArray);

        $expectedViewHelpers = [
            'optionValue',
            'fileUrl',
            'frontdoorUrl',
            'transferUrl',
            'dcmiType',
            'dcType',
            'openAireType',
        ];

        $options = $serverFactory->getFormatOptions();

        $this->assertEquals($expectedViewHelpers, $options['viewHelper']);
    }

    public function testViewHelpersConfiguredForMetadaPrefix()
    {
        $configArray = $this->getConfigurationArray();

        $configArray['oai']['format']['default'] = [
            'viewHelper' => 'optionValue, fileUrl, frontdoorUrl, transferUrl, dcmiType, dcType, openAireType',
        ];

        $configArray['oai']['format']['oai_dc'] = [
            'viewHelper' => 'optionValue, fileUrl, frontdoorUrl',
        ];

        $serverFactory = $this->createServerFactory($configArray);

        $expectedProcessors = [
            'optionValue',
            'fileUrl',
            'frontdoorUrl',
        ];

        $options = $serverFactory->getFormatOptions('oai_dc');

        $this->assertEquals($expectedProcessors, $options['viewHelper']);
    }
}
