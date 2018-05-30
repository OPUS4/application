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
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2008-2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Formular fuer einen Identifier eines Dokuments.
 *
 * @category    Application
 * @package     Module_Admin
 * @subpackage  Form_Document
 */
class Admin_Form_Document_Identifier extends Admin_Form_AbstractModelSubForm
{

    /**
     * Name fuer Formularelement fuer Identifier-Wert.
     */
    const ELEMENT_VALUE = 'Value';

    /**
     * Name fuer Formularelement fuer Identifier-Id.
     */
    const ELEMENT_ID = 'Id';

    /**
     * Name fuer Forumlarelement fuer Identifer-Typ.
     */
    const ELEMENT_TYPE = 'Type';

    /**
     * Erzeugt Elemente fuer Identifier Formular.
     */
    public function init()
    {
        parent::init();
        
        $typeElement = $this->createElement('Identifier', self::ELEMENT_TYPE, array('required' => true));
        $this->addElement($typeElement);
        
        $valueElement = $this->createElement('text', self::ELEMENT_VALUE, array(
            'required' => true, 'size' => '80'
        ));
        $valueElement->addValidator(new Application_Form_Validate_Identifier($typeElement));
        $this->addElement($valueElement);

        $this->addElement('hidden', self::ELEMENT_ID);
    }

    /**
     * Befuehlt Formularelement von Opus_Identifier Instanz.
     * @param Opus_Identifier $identifier
     */
    public function populateFromModel($identifier)
    {
        $this->getElement(self::ELEMENT_TYPE)->setValue($identifier->getType());
        $this->getElement(self::ELEMENT_VALUE)->setValue($identifier->getValue());
        $this->getElement(self::ELEMENT_ID)->setValue($identifier->getId());
    }

    /**
     * Aktualisiert Opus_Identifier Instanz aus Formularelementen.
     * @param Opus_Identifier $identifier
     */
    public function updateModel($identifier)
    {
        $value = $this->getElement(self::ELEMENT_TYPE)->getValue();
        $identifier->setType($value);

        $value = $this->getElement(self::ELEMENT_VALUE)->getValue();
        $identifier->setValue($value);
    }

    public function getModel()
    {
        $modelId = $this->getElement(self::ELEMENT_ID)->getValue();

        $identifier = null;

        if (strlen(trim($modelId)) > 0) {
            try {
                $identifier = new Opus_Identifier($modelId);
            } catch (Opus_Model_NotFoundException $omnfe) {
                $this->getLogger()->err(__METHOD__ . " Unknown identifier ID = '$modelId'.");
            }
        }

        if (is_null($identifier)) {
            $identifier = new Opus_Identifier();
        }

        $this->updateModel($identifier);

        return $identifier;
    }

    public function loadDefaultDecorators()
    {
        parent::loadDefaultDecorators();

        $this->removeDecorator('Fieldset');
    }

}
