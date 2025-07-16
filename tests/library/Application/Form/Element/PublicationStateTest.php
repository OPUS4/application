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
 * @copyright   Copyright (c) 2024, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\PublicationState;

class Application_Form_Element_PublicationStateTest extends FormElementTestCase
{
    /** @var string[] */
    protected $additionalResources = ['view', 'translation'];

    public function setUp(): void
    {
        $this->formElementClass       = 'Application_Form_Element_DocumentType';
        $this->expectedDecorators     = [
            'ViewHelper',
            'Errors',
            'Description',
            'ElementHtmlTag',
            'LabelNotEmpty',
            'dataWrapper',
            'ElementHint',
        ];
        $this->expectedDecoratorCount = count($this->expectedDecorators);
        $this->staticViewHelper       = 'viewFormSelect';
        parent::setUp();
    }

    public function testConfigureExcludedValues()
    {
        $this->adjustConfiguration([
            'model' => [
                'document' => [
                    'fields' => [
                        'PublicationState' => [
                            'excludeValues' => 'draft,authorsVersion',
                        ],
                    ],
                ],
            ],
        ]);

        $element = new Application_Form_Element_PublicationState('PublicationState');

        $options = $element->getMultiOptions();

        $this->assertCount(7, $options); // 6 + NULL

        $keys = array_keys($options);

        $this->assertNotContains('draft', $keys);
        $this->assertNotContains('authorsVersion', $keys);
    }

    public function testAllowExistingValueEvenIfExcluded()
    {
        $this->adjustConfiguration([
            'model' => [
                'document' => [
                    'fields' => [
                        'PublicationState' => [
                            'excludeValues' => 'draft,authorsVersion',
                        ],
                    ],
                ],
            ],
        ]);

        $element = new Application_Form_Element_PublicationState('PublicationState');

        $element->setValue('draft');

        $options = $element->getMultiOptions();

        $this->assertCount(8, $options); // 7 + NULL

        $keys = array_keys($options);

        $this->assertNotContains('authorsVersion', $keys);
        $this->assertContains('draft', $keys);
    }

    public function testOptionsTranslated()
    {
        $translator = Application_Translate::getInstance();

        $publicationState = new PublicationState();

        foreach ($publicationState->getAllValues() as $state) {
            $this->assertTrue($translator->isTranslated("Opus_Document_PublicationState_Value_{$state}"));
        }
    }
}
