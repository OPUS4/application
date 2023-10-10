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

    /**
     * @return array
     */
    protected function getTestConfiguration()
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
                        'class'                => MockDefaultOaiServer::class,
                        'viewHelpers'          => 'optionValue, fileUrl, frontdoorUrl, transferUrl, dcmiType, dcType, openAireType',
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
     * @param array|null $configuration
     * @return Oai_Model_OaiConfig
     */
    protected function getOaiConfig($configuration = null)
    {
        if ($configuration === null) {
            $config = new Zend_Config($this->getTestConfiguration());
        } else {
            $config = new Zend_Config($configuration);
        }

        $oaiCongig = Oai_Model_OaiConfig::getInstance();
        $oaiCongig->setConfig($config);

        return $oaiCongig;
    }

    public function testCreate()
    {
        $serverFactory = new Oai_Model_ServerFactory();
        $serverFactory->setOaiConfig($this->getOaiConfig());
        $server = $serverFactory->create();
        $this->assertEquals(MockDefaultOaiServer::class, get_class($server));
    }

    public function testCreateWithMetadataPrefix()
    {
        $serverFactory = new Oai_Model_ServerFactory();
        $serverFactory->setOaiConfig($this->getOaiConfig());
        $server = $serverFactory->create('xMetaDissPlus');
        $this->assertEquals(Oai_Model_Prefix_XMetaDissPlus_XMetaDissPlusServer::class, get_class($server));
    }

    public function testCreateWithUnknownMetadataPrefix()
    {
        $serverFactory = new Oai_Model_ServerFactory();
        $serverFactory->setOaiConfig($this->getOaiConfig());
        $server = $serverFactory->create('unknownPrefix');
        $this->assertEquals(MockDefaultOaiServer::class, get_class($server));
    }

    public function testCreateWithNoFormatConfiguration()
    {
        $testConfiguration = $this->getTestConfiguration();
        unset($testConfiguration['oai']['format']);
        $oaiConfig = $this->getOaiConfig($testConfiguration);

        $serverFactory = new Oai_Model_ServerFactory();
        $serverFactory->setOaiConfig($oaiConfig);
        $server = $serverFactory->create('xMetaDissPlus');

        $this->assertEquals(Oai_Model_DefaultServer::class, get_class($server));
    }

    public function testCreateWithUnknownFormatClass()
    {
        $testConfiguration                                            = $this->getTestConfiguration();
        $testConfiguration['oai']['format']['xmetadissplus']['class'] = 'UnknownClass';
        $oaiConfig     = $this->getOaiConfig($testConfiguration);
        $serverFactory = new Oai_Model_ServerFactory();
        $serverFactory->setOaiConfig($oaiConfig);
        $server = $serverFactory->create('xMetaDissPlus');
        $this->assertEquals(Oai_Model_DefaultServer::class, get_class($server));
    }

    public function testDefaultServerOptionsNoDefaultConfiguration()
    {
        $testConfiguration = $this->getTestConfiguration();
        unset($testConfiguration['oai']['format']['default']);
        $oaiConfig     = $this->getOaiConfig($testConfiguration);
        $serverFactory = new Oai_Model_ServerFactory();
        $serverFactory->setOaiConfig($oaiConfig);
        $server = $serverFactory->create();

        $expectedOptions = [
            'maxListIdentifiers'    => 10,
            'maxListRecords'        => 10,
            'resumptionTokenPath'   => '/vagrant/tests/workspace/tmp/resumption',
            'emailContact'          => 'opus4ci@example.org',
            'xsltFile'              => '',
            'documentStatesAllowed' => ['published', 'deleted'],
            'viewHelpers'           => ['listMetadataFormats'],
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
        $serverFactory = new Oai_Model_ServerFactory();
        $serverFactory->setOaiConfig($this->getOaiConfig());
        $server = $serverFactory->create();

        $expectedOptions = [
            'maxListIdentifiers'    => 10,
            'maxListRecords'        => 10,
            'resumptionTokenPath'   => '/vagrant/tests/workspace/tmp/resumption',
            'emailContact'          => 'opus4ci@example.org',
            'xsltFile'              => 'oaiFile.xslt',
            'documentStatesAllowed' => ['published', 'deleted'],
            'viewHelpers'           => ['optionValue', 'fileUrl', 'frontdoorUrl', 'transferUrl', 'dcmiType', 'dcType', 'openAireType', 'listMetadataFormats'],
            'notEmbargoedOn'        => false,
            'hasFilesVisibleInOai'  => true,
            'adminOnly'             => false,
            'visible'               => true,
            'checkEmbargo'          => false,
        ];

        $this->assertEquals(MockDefaultOaiServer::class, get_class($server));
        $this->assertEquals($expectedOptions, $server->getOptions(array_keys($expectedOptions)));
    }

    public function testFormatServerClassOverwritesDefaults()
    {
        $testConfiguration                                   = $this->getTestConfiguration();
        $testConfiguration['oai']['format']['xmetadissplus'] = [
            'class' => MockXMetaDissPlusServer::class,
        ];
        $oaiConfig                                           = $this->getOaiConfig($testConfiguration);
        $serverFactory                                       = new Oai_Model_ServerFactory();
        $serverFactory->setOaiConfig($oaiConfig);
        $server = $serverFactory->create('xmetadissplus');

        $expectedOptions = [
            'maxListIdentifiers'    => 10,
            'maxListRecords'        => 10,
            'resumptionTokenPath'   => '/vagrant/tests/workspace/tmp/resumption',
            'emailContact'          => 'opus4ci@example.org',
            'xsltFile'              => 'XMetaDissPlus.xslt',
            'documentStatesAllowed' => ['published', 'deleted'],
            'viewHelpers'           => ['optionValue', 'fileUrl', 'frontdoorUrl', 'listMetadataFormats'],
            'notEmbargoedOn'        => true,
            'hasFilesVisibleInOai'  => true,
            'adminOnly'             => false,
            'visible'               => true,
            'checkEmbargo'          => true,
            'prefixLabel'           => 'xMetaDissPlus',
            'schemaUrl'             => 'http://files.dnb.de/standards/xmetadissplus/xmetadissplus.xsd',
            'metadataNamespaceUrl'  => 'http://www.d-nb.de/standards/xmetadissplus/',
        ];

        $this->assertEquals(MockXMetaDissPlusServer::class, get_class($server));
        $this->assertEquals($expectedOptions, $server->getOptions(array_keys($expectedOptions)));
    }

    public function testFormatServerClassOptionsOverwrittenByFormatConfiguration()
    {
        $testConfiguration                                   = $this->getTestConfiguration();
        $testConfiguration['oai']['format']['xmetadissplus'] = [
            'class'        => MockXMetaDissPlusServer::class,
            'viewHelpers'  => 'additionalViewHelper1, additionalViewHelper2',
            'xsltFile'     => 'configuredXMetaDissPlus.xslt',
            'checkEmbargo' => 0,
        ];
        $oaiConfig                                           = $this->getOaiConfig($testConfiguration);
        $serverFactory                                       = new Oai_Model_ServerFactory();
        $serverFactory->setOaiConfig($oaiConfig);
        $server = $serverFactory->create('xmetadissplus');

        $expectedOptions = [
            'maxListIdentifiers'    => 10,
            'maxListRecords'        => 10,
            'resumptionTokenPath'   => '/vagrant/tests/workspace/tmp/resumption',
            'emailContact'          => 'opus4ci@example.org',
            'xsltFile'              => 'configuredXMetaDissPlus.xslt',
            'documentStatesAllowed' => ['published', 'deleted'],
            'viewHelpers'           => ['optionValue', 'fileUrl', 'frontdoorUrl', 'additionalViewHelper1', 'additionalViewHelper2', 'listMetadataFormats'],
            'notEmbargoedOn'        => true,
            'hasFilesVisibleInOai'  => true,
            'adminOnly'             => false,
            'visible'               => true,
            'checkEmbargo'          => false,
            'prefixLabel'           => 'xMetaDissPlus',
            'schemaUrl'             => 'http://files.dnb.de/standards/xmetadissplus/xmetadissplus.xsd',
            'metadataNamespaceUrl'  => 'http://www.d-nb.de/standards/xmetadissplus/',
        ];

        $this->assertEquals(MockXMetaDissPlusServer::class, get_class($server));
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
