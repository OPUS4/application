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

class Oai_Model_OaiConfigTest extends ControllerTestCase
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
                    'default'       => [
                        'class'      => DefaultOaiServer::class,
                        'viewHelper' => 'optionValue, fileUrl, frontdoorUrl, transferUrl, dcmiType, dcType, openAireType',
                        'xsltFile'   => 'oaiFile.xslt',
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

    public function testGetDefaults()
    {
        $oaiConfig = Oai_Model_OAIConfig::getInstance();
        $oaiConfig->setConfig(new Zend_Config($this->getConfigurationArray()));

        $defaults = $oaiConfig->getDefaults();

        $expectedDefaults = [
            'maxListIdentifiers'  => 10,
            'maxListRecords'      => 10,
            'resumptionTokenPath' => '/vagrant/tests/workspace/tmp/resumption',
            'emailContact'        => 'opus4ci@example.org',
            'xsltFile'            => 'oaiFile.xslt',
            'class'               => DefaultOaiServer::class,
            'viewHelper'          => 'optionValue, fileUrl, frontdoorUrl, transferUrl, dcmiType, dcType, openAireType',
        ];

        $this->assertEquals($expectedDefaults, $defaults);
    }

    public function testGetDefaultsNoFormatDefaults()
    {
        $oaiConfig = Oai_Model_OAIConfig::getInstance();

        $configArray = $this->getConfigurationArray();

        unset($configArray['oai']['format']['default']);

        $oaiConfig->setConfig(new Zend_Config($configArray));

        $expectedDefaults = [
            'maxListIdentifiers'  => 10,
            'maxListRecords'      => 10,
            'resumptionTokenPath' => '/vagrant/tests/workspace/tmp/resumption',
            'emailContact'        => 'opus4ci@example.org',
        ];

        $defaults = $oaiConfig->getDefaults();
        $this->assertEquals($expectedDefaults, $defaults);
    }

    public function testGetDefaultsNoOaiConfiguration()
    {
        $oaiConfig = Oai_Model_OAIConfig::getInstance();

        $configArray = $this->getConfigurationArray();

        unset($configArray['oai']);

        $oaiConfig->setConfig(new Zend_Config($configArray));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No configuration for module oai.');

        $oaiConfig->getDefaults();
    }

    public function testGetFormatOptions()
    {
        $oaiConfig = Oai_Model_OAIConfig::getInstance();
        $oaiConfig->setConfig(new Zend_Config($this->getConfigurationArray()));

        $expectedFormatOptions = [
            'class'                   => Oai_Model_Prefix_XMetaDissPlus_XMetaDissPlusServer::class,
            'xsltFile'                => 'XMetaDissPlus.xslt',
            'prefixLabel'             => 'xMetaDissPlus',
            'hasFilesVisibleInOai'    => 1,
            'checkEmbargo'            => 1,
            'notEmbargoedOn'          => 1,
            'schemaUrl'               => 'http://files.dnb.de/standards/xmetadissplus/xmetadissplus.xsd',
            'setMetadataNamespaceUrl' => 'http://www.d-nb.de/standards/xmetadissplus/',
        ];

        $formatOptions = $oaiConfig->getFormatOptions('xMetaDissPlus');
        $this->assertEquals($expectedFormatOptions, $formatOptions);
    }

    public function testGetFormatOptionsUnknownPrefix()
    {
        $oaiConfig = Oai_Model_OAIConfig::getInstance();
        $oaiConfig->setConfig(new Zend_Config($this->getConfigurationArray()));

        $formatOptions = $oaiConfig->getFormatOptions('unknownPrefix');
        $this->assertEquals([], $formatOptions);
    }

    public function testGetFormatOptionsNoFormatConfiguration()
    {
        $oaiConfig = Oai_Model_OAIConfig::getInstance();

        $configArray = $this->getConfigurationArray();
        unset($configArray['oai']['format']);

        $oaiConfig->setConfig(new Zend_Config($configArray));

        $formatOptions = $oaiConfig->getFormatOptions('xMetaDissPlus');
        $this->assertEquals([], $formatOptions);
    }

    public function testGetFormatOptionsWithWrongPrefixCaseInConfiguration()
    {
        $oaiConfig = Oai_Model_OAIConfig::getInstance();

        $configArray                            = $this->getConfigurationArray();
        $configArray['oai']['format']['EPICUR'] = [
            'class' => Oai_Model_Prefix_Epicur_EpicurServer::class,
        ];

        $oaiConfig->setConfig(new Zend_Config($configArray));

        $expectedFormatOptions = [
            'class' => Oai_Model_Prefix_Epicur_EpicurServer::class,
        ];

        $formatOptions = $oaiConfig->getFormatOptions('epicur');
        $this->assertEquals($expectedFormatOptions, $formatOptions);
    }
}
