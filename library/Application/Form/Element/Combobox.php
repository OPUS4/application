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
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Combobox element providing list of values like a select, but also allowing entering new values.
 *
 * The rendering could be done using jQuery-UI.
 *
 * TODO or maybe the "datalist" feature of HTML 5.
 *
 * A combobox has options like a select element, but it can also have a value that does not match any of the options.
 */
class Application_Form_Element_Combobox extends Zend_Form_Element_Multi
{
    /** @var bool */
    public $multiple = false;

    /** @var string */
    public $helper = 'formCombobox';

    public function init()
    {
        $this->setAutoInsertNotEmptyValidator(false);
        $this->setRegisterInArrayValidator(false);

        parent::init();

        $this->addPrefixPath('Application_Form_Decorator', 'Application/Form/Decorator', Zend_Form::DECORATOR);
    }

    public function loadDefaultDecorators()
    {
        if (! $this->loadDefaultDecoratorsIsDisabled() && count($this->getDecorators()) === 0) {
            $this->setDecorators(
                [
                    'ViewHelper',
                    'Description',
                    'Errors',
                    'ElementHtmlTag',
                    ['LabelNotEmpty', ['tag' => 'div', 'tagClass' => 'label', 'placement' => 'prepend']],
                    [['dataWrapper' => 'HtmlTagWithId'], ['tag' => 'div', 'class' => 'data-wrapper']],
                ]
            );
        }
    }

    /**
     * Sets multi option such that value and label are equal.
     *
     * @param array|string|null $values
     */
    public function setAutocompleteValues($values)
    {
        if ($values !== null) {
            if (is_array($values)) {
                $options = array_combine($values, $values);
                $options = array_diff($options, [null]); // remove options with null value
            } else {
                $options = [$values => $values];
            }

            $this->setMultiOptions($options);
        }
    }
}
