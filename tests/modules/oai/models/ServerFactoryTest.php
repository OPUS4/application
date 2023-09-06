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
    /** @var string[] */
    protected $additionalResources = ['database', 'view'];

    public function setUp(): void
    {
        parent::setUp();
    }

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
                    'default'       => [
                        'class'                => DefaultOaiServer::class,
                        'viewHelper'           => 'optionValue, fileUrl, frontdoorUrl, transferUrl, dcmiType, dcType, openAireType',
                        'xsltFile'             => 'oaiFile.xslt',
                        'hasFilesVisibleInOai' => 1,
                    ],
                    'copy_xml'      => [
                        'xsltFile'  => 'copy_xml.xslt',
                        'adminOnly' => 1,
                        'visible'   => 0,
                    ],
                    'xmetadissplus' => [
                        'class'                   => Oai_Model_Prefix_XMetaDissPlus_XMetaDissPlusServer::class,
                        'xsltFile'                => 'XMetaDissPlus.xslt',
                        'prefixLabel'             => 'xMetaDissPlus',
                        'hasFilesVisibleInOai'    => 1,
                        'checkEmbargo'            => 1,
                        'notEmbargoedOn'          => 1,
                        'schemaUrl'               => 'http://files.dnb.de/standards/xmetadissplus/xmetadissplus.xsd',
                        'setMetadataNamespaceUrl' => 'http://www.d-nb.de/standards/xmetadissplus/',
                    ],
                    'oai_pp'        => null,
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
        $serverFactory = new Oai_Model_ServerFactory();

        if ($configurationArray === null) {
            $config = new Zend_Config($this->getConfigurationArray());
        } else {
            $config = new Zend_Config($configurationArray);
        }

        $serverFactory->setConfig($config);

        return $serverFactory;
    }

    public function testGetFormats()
    {
        $configArray = [
            'oai' => [
                'format' => [
                    'Default'        => [
                        'class' => Oai_Model_DefaultServer::class,
                    ],
                    'copy_xml'       => [
                        'xsltFile' => 'copy_xml.xslt',
                    ],
                    'oai_dc'         => [
                        'class' => Oai_Model_DefaultServer::class,
                    ],
                    'epicur'         => [
                        'class' => Oai_Model_DefaultServer::class,
                    ],
                    'xMetaDissPluss' => [
                        'class' => Oai_Model_DefaultServer::class,
                    ],
                ],
            ],
        ];

        $serverFactory = $this->createServerFactory($configArray);

        $formats = $serverFactory->getFormats();

        $expectedFormats = ['copy_xml', 'oai_dc', 'epicur', 'xmetadisspluss'];
        $this->assertEquals($expectedFormats, $formats);
    }

    public function testCreate()
    {
        $serverFactory = $this->createServerFactory();
        $server        = $serverFactory->create();
        $this->assertEquals(Oai_Model_DefaultServer::class, get_class($server));
    }

    public function testCreateWithMetadataPrefix()
    {
        $serverFactory = $this->createServerFactory();
        $server        = $serverFactory->create('xMetaDissPlus');
        $this->assertEquals(Oai_Model_Prefix_XMetaDissPlus_XMetaDissPlusServer::class, get_class($server));
    }

    public function testCreateWithUnknownMetadataPrefix()
    {
        $serverFactory = $this->createServerFactory();
        $server        = $serverFactory->create('unknownPrefix');
        $this->assertEquals(Oai_Model_DefaultServer::class, get_class($server));
    }

    public function testCreateWithNoFormatConfiguration()
    {
        $configArray = $this->getConfigurationArray();
        unset($configArray['oai']['format']);
        $serverFactory = $this->createServerFactory($configArray);
        $server        = $serverFactory->create('xMetaDissPlus');
        $this->assertEquals(Oai_Model_DefaultServer::class, get_class($server));
    }

    public function testCreateWithUnkownFormatClass()
    {
        $configArray                                            = $this->getConfigurationArray();
        $configArray['oai']['format']['xmetadissplus']['class'] = 'UnknownClass';
        $serverFactory                                          = $this->createServerFactory($configArray);
        $server                                                 = $serverFactory->create('xMetaDissPlus');
        $this->assertEquals(Oai_Model_DefaultServer::class, get_class($server));
    }

    public function testDefaultServerOptionsNoDefaultConfiguration()
    {
        $configArray = $this->getConfigurationArray();

        unset($configArray['oai']['format']['default']);

        $serverFactory = $this->createServerFactory($configArray);
        $server        = $serverFactory->create();

        $expectedOptions = [
            'maxListIdentifiers'    => 10,
            'maxListRecords'        => 10,
            'resumptionTokenPath'   => '/vagrant/tests/workspace/tmp/resumption',
            'emailContact'          => 'opus4ci@example.org',
            'xsltFile'              => '',
            'documentStatesAllowed' => ['published', 'deleted'],
            'viewHelper'            => ['listMetadataFormats'],
            'notEmbargoedOn'        => false,
            'hasFilesVisibleInOai'  => false,
            'adminOnly'             => false,
            'visible'               => true,
            'checkEmbargo'          => false,
        ];

        $this->assertEquals(Oai_Model_DefaultServer::class, get_class($server));
        $this->assertEquals($expectedOptions, $server->getOptions(array_keys($expectedOptions)));
    }

    public function testDefaultServerOptionsWithDefaultConfiguration()
    {
        $configArray   = $this->getConfigurationArray();
        $serverFactory = $this->createServerFactory($configArray);
        $server        = $serverFactory->create();

        $expectedOptions = [
            'maxListIdentifiers'    => 10,
            'maxListRecords'        => 10,
            'resumptionTokenPath'   => '/vagrant/tests/workspace/tmp/resumption',
            'emailContact'          => 'opus4ci@example.org',
            'xsltFile'              => 'oaiFile.xslt',
            'documentStatesAllowed' => ['published', 'deleted'],
            'viewHelper'            => ['optionValue', 'fileUrl', 'frontdoorUrl', 'transferUrl', 'dcmiType', 'dcType', 'openAireType', 'listMetadataFormats'],
            'notEmbargoedOn'        => false,
            'hasFilesVisibleInOai'  => true,
            'adminOnly'             => false,
            'visible'               => true,
            'checkEmbargo'          => false,
        ];

        $this->assertEquals(Oai_Model_DefaultServer::class, get_class($server));
        $this->assertEquals($expectedOptions, $server->getOptions(array_keys($expectedOptions)));
    }

    public function testFormatServerClassOverwritesDefaults()
    {
        $configArray = $this->getConfigurationArray();

        $configArray['oai']['format']['xmetadissplus'] = [
            'class' => XMetaDissPlusServer::class,
        ];

        $serverFactory = $this->createServerFactory($configArray);
        $server        = $serverFactory->create('xmetadissplus');

        $expectedOptions = [
            'maxListIdentifiers'    => 10,
            'maxListRecords'        => 10,
            'resumptionTokenPath'   => '/vagrant/tests/workspace/tmp/resumption',
            'emailContact'          => 'opus4ci@example.org',
            'xsltFile'              => 'XMetaDissPlus.xslt',
            'documentStatesAllowed' => ['published', 'deleted'],
            'viewHelper'            => ['optionValue', 'fileUrl', 'frontdoorUrl', 'listMetadataFormats'],
            'notEmbargoedOn'        => true,
            'hasFilesVisibleInOai'  => true,
            'adminOnly'             => false,
            'visible'               => true,
            'checkEmbargo'          => true,
            'prefixLabel'           => 'xMetaDissPlus',
            'schemaUrl'             => 'http://files.dnb.de/standards/xmetadissplus/xmetadissplus.xsd',
            'metadataNamespaceUrl'  => 'http://www.d-nb.de/standards/xmetadissplus/',
        ];

        $this->assertEquals(XMetaDissPlusServer::class, get_class($server));
        $this->assertEquals($expectedOptions, $server->getOptions(array_keys($expectedOptions)));
    }

    public function testFormatServerClassOptionsOverwrittenByFormatConfiguration()
    {
        $configArray = $this->getConfigurationArray();

        $configArray['oai']['format']['xmetadissplus'] = [
            'class'        => XMetaDissPlusServer::class,
            'viewHelper'   => 'additionalViewHelper1, additionalViewHelper2',
            'xsltFile'     => 'configuredXMetaDissPlus.xslt',
            'checkEmbargo' => 0,
        ];

        $serverFactory = $this->createServerFactory($configArray);
        $server        = $serverFactory->create('xmetadissplus');

        $expectedOptions = [
            'maxListIdentifiers'    => 10,
            'maxListRecords'        => 10,
            'resumptionTokenPath'   => '/vagrant/tests/workspace/tmp/resumption',
            'emailContact'          => 'opus4ci@example.org',
            'xsltFile'              => 'configuredXMetaDissPlus.xslt',
            'documentStatesAllowed' => ['published', 'deleted'],
            'viewHelper'            => ['optionValue', 'fileUrl', 'frontdoorUrl', 'additionalViewHelper1', 'additionalViewHelper2', 'listMetadataFormats'],
            'notEmbargoedOn'        => true,
            'hasFilesVisibleInOai'  => true,
            'adminOnly'             => false,
            'visible'               => true,
            'checkEmbargo'          => false,
            'prefixLabel'           => 'xMetaDissPlus',
            'schemaUrl'             => 'http://files.dnb.de/standards/xmetadissplus/xmetadissplus.xsd',
            'metadataNamespaceUrl'  => 'http://www.d-nb.de/standards/xmetadissplus/',
        ];

        $this->assertEquals(XMetaDissPlusServer::class, get_class($server));
        $this->assertEquals($expectedOptions, $server->getOptions(array_keys($expectedOptions)));
    }

    public function testDoViewHelpersExist()
    {
        $viewHelpers         = ['optionValue', 'fileUrl', 'frontdoorUrl', 'transferUrl', 'dcmiType', 'dcType', 'openAireType'];
        $viewHelpersNotFound = [];

        foreach ($viewHelpers as $viewHelper) {
            try {
                Zend_Registry::get('Opus_View')->getHelper($viewHelper);
            } catch (Zend_Loader_PluginLoader_Exception $e) {
                $viewHelpersNotFound[] = $viewHelper;
            }
        }

        $this->assertEquals([], $viewHelpersNotFound);
    }
}
