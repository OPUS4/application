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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Form for editing selected OPUS 4 configuration options.
 *
 * TODO Application_Form_Abstract should be enough (not ID element needed)
 */
class Admin_Form_Configuration extends Application_Form_Model_Abstract
{
    /**
     * Prefix for translation keys of configuration options.
     *
     * TODO wird auf von Admin_Model_Option verwendet
     */
    public const LABEL_TRANSLATION_PREFIX = 'admin_config_';

    /** @var array Configured options for form. */
    private $options;

    /**
     * @param null|Zend_Config $config
     */
    public function __construct($config = null)
    {
        if ($config !== null) {
            $options       = new Admin_Model_Options($config);
            $this->options = $options->getOptions();
        }

        parent::__construct();
    }

    /**
     * Configures form and creates form elements.
     */
    public function init()
    {
        parent::init();

        if ($this->options === null) {
            $options       = new Admin_Model_Options();
            $this->options = $options->getOptions();
        }

        foreach ($this->options as $name => $option) {
            $section = $option->getSection();

            $element = $this->createElement(
                $option->getElementType(),
                $name,
                array_merge(
                    [
                        'label'       => $option->getLabel(),
                        'description' => $option->getDescription(),
                    ],
                    $option->getOptions()
                )
            );

            $this->addElement($element);
            $this->addElementToSection($element, $section);
        }

        $this->removeElement(self::ELEMENT_MODEL_ID);

        $this->setAttrib('class', 'admin_config');
    }

    /**
     * Initializes values of form elements from configuration.
     *
     * @param Zend_Config $config
     */
    public function populateFromModel($config)
    {
        foreach ($this->options as $name => $option) {
            $value = Application_Configuration::getValueFromConfig($config, $option->getKey());
            $this->getElement($name)->setValue($value);
        }
    }

    /**
     * Updates configuration with values from form elements.
     *
     * @param Zend_Config $config
     */
    public function updateModel($config)
    {
        foreach ($this->options as $name => $option) {
            $value = $this->getElement($name)->getValue();

            // TODO move into Admin_Model_Option?
            if (is_array($value)) {
                $value = implode(',', $value);
            }

            Application_Configuration::setValueInConfig($config, $option->getKey(), $value);
        }
    }

    /**
     * Adds an element to a section (display group) of the form.
     *
     * If necessary a new display group is created.
     *
     * @param Zend_Form_Element $element Form element
     * @param string            $section Name of section
     * @throws Zend_Form_Exception
     */
    public function addElementToSection($element, $section)
    {
        $group = $this->getDisplayGroup($section);

        if ($group === null) {
            $this->addDisplayGroup(
                [$element],
                $section,
                [
                    'legend'     => self::LABEL_TRANSLATION_PREFIX . 'section_' . $section,
                    'decorators' => ['FormElements', 'Fieldset'],
                ]
            );
        } else {
            $group->addElement($element);
        }
    }
}
