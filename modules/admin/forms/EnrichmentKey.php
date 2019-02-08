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
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Form for creating and editing an enrichment key.
 *
 * @category    Application
 * @package     Module_Admin
 */
class Admin_Form_EnrichmentKey extends Application_Form_Model_Abstract {

    /**
     * Form element name for enrichment key name.
     */
    const ELEMENT_NAME = 'Name';

    /**
     * Pattern for checking valid enrichment key names.
     *
     * Enrichment key have to start with a letter and can use letters, numbers and '.' and '_'.
     */
    const PATTERN = '/^[a-zA-Z][a-zA-Z0-9_\.]+$/';

    /**
     * Initialize form elements.
     * @throws Zend_Form_Exception
     */
    public function init() {
        parent::init();

        $this->setLabelPrefix('Opus_EnrichmentKey');
        $this->setUseNameAsLabel(true);
        $this->setModelClass('Opus_EnrichmentKey');
        $this->setVerifyModelIdIsNumeric(false);

        $name = $this->createElement('text', self::ELEMENT_NAME, [
            'required' => true, 'label' => 'admin_enrichmentkey_label_name',
            'maxlength' => Opus_EnrichmentKey::getFieldMaxLength('Name')
        ]);
        $name->addValidator('regex', false, array('pattern' => self::PATTERN));
        $name->addValidator('StringLength', false, [
            'min' => 1, 'max' => Opus_EnrichmentKey::getFieldMaxLength('Name')
        ]);
        $name->addValidator(new Application_Form_Validate_EnrichmentKeyAvailable());
        $this->addElement($name);
    }

    /**
     * Initialisiert das Formular mit Werten einer Model-Instanz.
     * @param $model Opus_Enrichmentkey
     */
    public function populateFromModel($enrichmentKey) {
        $this->getElement(self::ELEMENT_MODEL_ID)->setValue($enrichmentKey->getName());
        $this->getElement(self::ELEMENT_NAME)->setValue($enrichmentKey->getName());
    }

    /**
     * Aktualsiert Model-Instanz mit Werten im Formular.
     * @param $model Opus_Enrichmentkey
     */
    public function updateModel($enrichmentKey) {
        $enrichmentKey->setName($this->getElementValue(self::ELEMENT_NAME));
    }

}
