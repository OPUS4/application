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
 * @copyright   Copyright (c) 2025, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_View_Helper_RenderValueTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'view';

    /** @var Application_View_Helper_RenderValue */
    private $viewHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->viewHelper = new Application_View_Helper_RenderValue();
    }

    public function testGetViewHelper()
    {
        $helper = $this->viewHelper->getViewHelper('orcidLink');
        $this->assertInstanceOf(Application_View_Helper_OrcidLink::class, $helper);

        $helper = $this->viewHelper->getViewHelper('OrcidLink');
        $this->assertInstanceOf(Application_View_Helper_OrcidLink::class, $helper);

        $helper = $this->viewHelper->getViewHelper('OrcidLink2');
        $this->assertNull($helper);
    }

    public function testGetModelConfig()
    {
        $modelConfig = $this->viewHelper->getModelConfig();

        $this->assertNotNull($modelConfig);
        $this->assertInstanceOf(Zend_Config::class, $modelConfig);
    }

    public function testSetModelConfig()
    {
        $testConfig = new Zend_Config([
            'model' => ['type' => ['URL' => ['viewHelper' => 'renderUrl']]],
        ]);

        $this->viewHelper->setModelConfig($testConfig);

        $modelConfig = $this->viewHelper->getModelConfig();

        $this->assertSame($testConfig, $modelConfig);
    }

    public function testGetFieldConfig()
    {
        $testConfig = new Zend_Config([
            'model' => [
                'type'       => ['URL' => ['viewHelper' => 'renderUrl']],
                'enrichment' => ['SourceSwb' => ['viewHelper' => 'renderUrl']],
            ],
        ]);

        $this->viewHelper->setModelConfig($testConfig);

        $fieldConfig = $this->viewHelper->getFieldConfig('SourceSwb');

        $this->assertNotNull($fieldConfig);
        $this->assertInstanceOf(Zend_Config::class, $fieldConfig);
        $this->assertEquals('renderUrl', $fieldConfig->viewHelper);
    }

    public function testGetFieldConfigWithType()
    {
        $testConfig = new Zend_Config([
            'model' => [
                'type'       => ['URL' => ['viewHelper' => 'renderUrl']],
                'enrichment' => ['SourceSwb' => ['type' => 'URL']],
            ],
        ]);

        $this->viewHelper->setModelConfig($testConfig);

        $fieldConfig = $this->viewHelper->getFieldConfig('SourceSwb');

        $this->assertNotNull($fieldConfig);
        $this->assertInstanceOf(Zend_Config::class, $fieldConfig);
        $this->assertEquals('URL', $fieldConfig->type);
        $this->assertEquals('renderUrl', $fieldConfig->viewHelper);
    }

    public function testGetFieldConfigOverridingTypeDefaults()
    {
        $testConfig = new Zend_Config([
            'model' => [
                'type'       => [
                    'URL' => [
                        'viewHelper' => 'renderUrl',
                        'escape'     => true,
                    ],
                ],
                'enrichment' => [
                    'SourceSwb' => [
                        'type'   => 'URL',
                        'escape' => false,
                    ],
                ],
            ],
        ]);

        $this->viewHelper->setModelConfig($testConfig);

        $fieldConfig = $this->viewHelper->getFieldConfig('SourceSwb');

        $this->assertNotNull($fieldConfig);
        $this->assertInstanceOf(Zend_Config::class, $fieldConfig);
        $this->assertEquals('URL', $fieldConfig->type);
        $this->assertEquals('renderUrl', $fieldConfig->viewHelper);
        $this->assertFalse($fieldConfig->escape);
    }

    public function testRenderValueFieldNotConfigured()
    {
        $this->assertEquals(
            'testValue',
            $this->viewHelper->renderValue('testValue', 'TestField')
        );
    }

    public function testRenderValueEscapeByDefault()
    {
        $this->assertEquals(
            '&lt;b&gt;Text&lt;/b&gt;',
            $this->viewHelper->renderValue('<b>Text</b>', 'TestField')
        );
    }

    public function testRenderValueFieldConfigured()
    {
        $testConfig = new Zend_Config([
            'model' => ['enrichment' => ['TestUrl' => ['viewHelper' => 'renderUrl']]],
        ]);

        $this->viewHelper->setModelConfig($testConfig);

        $url = 'http://test.org';

        $output = $this->viewHelper->renderValue($url, 'TestUrl');

        $this->assertEquals("<a href=\"{$url}\" target=\"_blank\">{$url}</a>", $output);
    }

    public function testRenderValueFieldConfiguredWithEscaping()
    {
        $testConfig = new Zend_Config([
            'model' => [
                'enrichment' => [
                    'TestUrl' => [
                        'viewHelper' => 'renderUrl',
                        'escape'     => true,
                    ],
                ],
            ],
        ]);

        $this->viewHelper->setModelConfig($testConfig);

        $url = 'http://test.org';

        $output = $this->viewHelper->renderValue($url, 'TestUrl');

        $this->assertEquals("&lt;a href=&quot;{$url}&quot; target=&quot;_blank&quot;&gt;{$url}&lt;/a&gt;", $output);
    }

    public function testRenderValueFieldConfiguredUnknownViewHelper()
    {
        $testConfig = new Zend_Config([
            'model' => ['enrichment' => ['TestUrl' => ['viewHelper' => 'renderUrl2']]],
        ]);

        $this->viewHelper->setModelConfig($testConfig);

        $url = 'http://test.org';

        $output = $this->viewHelper->renderValue($url, 'TestUrl');

        $this->assertEquals($url, $output);
    }
}
