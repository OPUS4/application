<?PHP

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
 * Form element for password entry.
 *
 * Used for forms that allow modifying or setting passwords. Passwords have to meet the following criteria:
 *
 * - must be at least 6 characters long
 */
class Application_Form_Element_Password extends Zend_Form_Element_Password
{
    /**
     * Initializes form element.
     */
    public function init()
    {
        parent::init();

        $this->addPrefixPath('Application_Form_Decorator', 'Application/Form/Decorator', Zend_Form::DECORATOR);
        $this->setAllowEmpty(false);
        $this->addValidator('stringLength', false, ['min' => 6]);

        $messageTemplates = [
            'stringLengthInvalid'  => 'validation_error_stringLengthInvalid',
            'stringLengthTooShort' => 'validation_error_stringLengthTooShort',
            'stringLengthTooLong'  => 'validation_error_stringLengthTooLong',
        ];

        $this->getValidator('StringLength')->setMessages($messageTemplates);
    }

    /**
     * Lädt die Defaultdekoratoren für ein Textelement.
     */
    public function loadDefaultDecorators()
    {
        if (! $this->loadDefaultDecoratorsIsDisabled() && count($this->getDecorators()) === 0) {
            $this->setDecorators(
                [
                    'ViewHelper',
                    'Placeholder',
                    'Description',
                    'ElementHint',
                    'Errors',
                    'ElementHtmlTag',
                    ['LabelNotEmpty', ['tag' => 'div', 'tagClass' => 'label', 'placement' => 'prepend']],
                    [['dataWrapper' => 'HtmlTagWithId'], ['tag' => 'div', 'class' => 'data-wrapper']],
                ]
            );
        }
    }
}
