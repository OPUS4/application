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
 * TODO deal with section name
 * TODO configure options
 */
class Admin_Form_Configuration extends Application_Form_Model_Abstract {

    /**
     * Prefix for translation keys of configuration options.
     */
    const LABEL_TRANSLATION_PREFIX = 'admin_config_';

    const ELEMENT_SAVE = 'Save';

    const ELEMENT_CANCEL = 'Cancel';

    private $options;

    /**
     * Configures form and creates form elements.
     */
    public function init() {
        parent::init();

        $this->options = array(
            'maxSearchResults' => 'searchengine.solr.numberOfDefaultSearchResults',
            'supportedLanguages' => 'supportedLanguages'
        );

        foreach ($this->options as $name => $option) {
            $this->addElement('text', $name, array('label' => $this->getOptionLabel($name),
                'description' => $this->getOptionDescription($name)));
        }

        $this->addElement('submit', self::ELEMENT_SAVE);
        $this->addElement('submit', self::ELEMENT_CANCEL);
    }

    /**
     * Initializes values of form elements from configuration.
     */
    public function populateFromModel($config) {
        foreach ($this->options as $name => $option) {
            $value = Application_Configuration::getValueFromConfig($config, $option);
            $this->getElement($name)->setValue($value);
        }
    }

    /**
     * Updates configuration with values from form elements.
     */
    public function updateModel($config) {
        foreach ($this->options as $name => $option) {
            $value = $this->getElement($name)->getValue();
            Application_Configuration::setValueInConfig($config, $option, $value);
        }
    }

    /**
     * Returns label name for option.
     *
     * @param $name
     * @return string
     */
    protected function getOptionLabel($name) {
        return self::LABEL_TRANSLATION_PREFIX . $name;
    }

    protected function getOptionDescription($name) {
        return self::LABEL_TRANSLATION_PREFIX . $name . '_description';
    }

}
