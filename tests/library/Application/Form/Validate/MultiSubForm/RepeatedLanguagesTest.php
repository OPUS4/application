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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Unit Tests für Klasse, die Unterformular auf Prüfung für wiederholte Sprachen vorbereitet.
 */
class Application_Form_Validate_MultiSubForm_RepeatedLanguagesTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['view', 'translation'];

    public function testImplementsInterface()
    {
        $instance = new Application_Form_Validate_MultiSubForm_RepeatedLanguages();

        $this->assertTrue($instance instanceof Application_Form_Validate_MultiSubFormInterface);
    }

    public function testIsValidReturnsTrue()
    {
        $instance = new Application_Form_Validate_MultiSubForm_RepeatedLanguages();

        $this->assertTrue($instance->isValid(null));
    }

    public function testGetSelectedLanguages()
    {
        $post = [
            'TitleMain0' => [
                'Id'       => '1',
                'Language' => 'deu',
                'Value'    => 'Titel 1',
            ],
            'TitleMain1' => [
                'Id'       => '2',
                'Language' => 'fra',
                'Value'    => 'Titel 2',
            ],
            'TitleMain2' => [
                'Id'       => '3',
                'Language' => 'rus',
                'Value'    => 'Titel 3',
            ],
        ];

        $instance = new Application_Form_Validate_MultiSubForm_RepeatedLanguages();

        $languages = $instance->getSelectedLanguages($post);

        $this->assertEquals(3, count($languages));
        $this->assertEquals('deu', $languages[0]);
        $this->assertEquals('fra', $languages[1]);
        $this->assertEquals('rus', $languages[2]);
    }

    /**
     * Jedem Language-Element in den Unterformularen wird ein Validator hinzugefügt. Formulare ohne Language-Element
     * werden ignoriert.
     */
    public function testPrepareValidation()
    {
        $form = new Zend_Form();

        $titleCount = 3;

        for ($index = 0; $index < $titleCount; $index++) {
            $subform = new Zend_Form_SubForm();
            $subform->addElement(new Application_Form_Element_Language('Language'));
            $form->addSubForm($subform, 'Title' . $index);
        }

        $subform = new Zend_Form_Subform();
        $subform->addElement('submit', 'Add');
        $form->addSubForm($subform, 'Actions');

        $instance = new Application_Form_Validate_MultiSubForm_RepeatedLanguages();

        $post = [
            'Title0'  => [
                'Id'       => '1',
                'Language' => 'deu',
                'Value'    => 'Titel 1',
            ],
            'Title1'  => [
                'Id'       => '2',
                'Language' => 'fra',
                'Value'    => 'Titel 2',
            ],
            'Title2'  => [
                'Id'       => '3',
                'Language' => 'rus',
                'Value'    => 'Titel 3',
            ],
            'Actions' => [
                'Add' => 'Add',
            ],
        ];

        $instance->prepareValidation($form, $post);

        for ($index = 0; $index < $titleCount; $index++) {
            $subform   = $form->getSubForm('Title' . $index);
            $validator = $subform->getElement('Language')->getValidator(
                'Application_Form_Validate_LanguageUsedOnceOnly'
            );
            $this->assertNotNull($validator);
            $this->assertEquals($index, $validator->getPosition());
            $this->assertEquals(['deu', 'fra', 'rus'], $validator->getLanguages());
        }
    }
}
