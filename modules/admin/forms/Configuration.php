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
 * @category    Application
 * @package     Module_Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2015, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Form for editing selected OPUS 4 configuration options.
 *
 * TODO Application_Form_Abstract should be enough (not ID element needed)
 */
class Admin_Form_Configuration extends Application_Form_Model_Abstract {

    /**
     * Prefix for translation keys of configuration options.
     *
     * TODO wird auf von Admin_Model_Option verwendet
     */
    const LABEL_TRANSLATION_PREFIX = 'admin_config_';

    /**
     * Configured options for form.
     * @var array
     */
    private $_options;

    /**
     * Configures form and creates form elements.
     */
    public function init() {
        parent::init();

        $options = new Admin_Model_Options();

        $this->_options = $options->getOptions();

        foreach ($this->_options as $name => $option) {
            $section = $option->getSection();

            $element = $this->createElement(
                $option->getElementType(),
                $name,
                array_merge(array(
                    'label' => $option->getLabel(),
                    'description' => $option->getDescription()
                    ),
                    $option->getOptions()
                )
            );

            $this->addElement($element);
            $this->addElementToSection($element, $section);
        }

        $this->removeElement(self::ELEMENT_MODEL_ID);
    }

    /**
     * Initializes values of form elements from configuration.
     */
    public function populateFromModel($config) {
        foreach ($this->_options as $name => $option) {
            $value = Application_Configuration::getValueFromConfig($config, $option->getKey());
            $this->getElement($name)->setValue($value);
        }
    }

    /**
     * Updates configuration with values from form elements.
     */
    public function updateModel($config) {
        foreach ($this->_options as $name => $option) {
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
     * @param $element Form element
     * @param $section Name of section
     * @throws Zend_Form_Exception
     */
    public function addElementToSection($element, $section)
    {
        $group = $this->getDisplayGroup($section);

        if (is_null($group)) {
            $this->addDisplayGroup(
                array($element),
                $section,
                array(
                    'legend' => self::LABEL_TRANSLATION_PREFIX . 'section_' . $section,
                    'decorators' => array('FormElements', 'Fieldset')
                )
            );
        }
        else {
            $group->addElement($element);
        }
    }

}
