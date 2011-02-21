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
 * @package     Tests
 * @author      Ralf Claußnitzer <ralf.claussnitzer@slub-dresden.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Domain model for Form_Builder test.
 *
 * @category    Application
 * @package     Tests
 *
 */
class LibraryTests_Form_BuilderTest_Model extends Opus_Model_AbstractDb {


    /**
     * Contains mock up data for MultiField.
     *
     * @var array
     */
    protected $_multiFieldValue = array();

    /**
     * Contains mock up data for MultiModel.
     *
     * @var array
     */
    protected $_multiModelValue = array();


    protected static $_tableGatewayClass = 'LibraryTests_Form_BuilderTest_DbModel';

    /**
     * Mock external field "ReferenceField".
     *
     * @var array
     */
    protected $_externalFields = array(
        'ReferenceField' => array(
            'model' => 'LibraryTests_Form_BuilderTest_DisconnectedModel'),
        'MultiField' => array(),
        'MultiModel' => array(
            'model' => 'LibraryTests_Form_BuilderTest_DisconnectedModel'));

    /**
     * Initialize model with the following fields:
     * - SimpleField
     * - ReferenceField
     *
     * @return void
     */
    protected function _init() {
        $simpleField = new Opus_Model_Field('SimpleField');

        $multiField = new Opus_Model_Field('MultiField');
        $multiField->setMultiplicity('*');

        $multiModel = new Opus_Model_Field('MultiModel');
        $multiModel->setValueModelClass('LibraryTests_Form_BuilderTest_DisconnectedModel');
        $multiModel->setMultiplicity('*');

        $referenceField = new Opus_Model_Field('ReferenceField');

        $this->addField($simpleField)
            ->addField($multiField)
            ->addField($multiModel)
            ->addField($referenceField);

    }

    /**
     * Mock function. Nothing is stored anywhere.
     *
     * @param Mixed $value Whatever data.
     * @see    Opus_Model_Abstract::$_externalFields
     * @return void
     */
    protected function _storeReferenceField($value) {

    }

    /**
     * Set up "ReferenceField" with an instance of Opus_Form_BuilderTest_DisconnectedModel.
     *
     * @see    Opus_Model_Abstract::$_externalFields
     * @return Opus_Form_BuilderTest_DisconnectedModel A mock model.
     */
    protected function _fetchReferenceField() {
        $mockModel = new LibraryTests_Form_BuilderTest_DisconnectedModel();
        return $mockModel;
    }


    /**
     * Mock function.
     *
     * @param Mixed $value Whatever data.
     * @see    Opus_Model_Abstract::$_externalFields
     * @return void
     */
    protected function _storeMultiField($value) {
    }

    /**
     * Return an array with mock field data.
     *
     * @see    Opus_Model_Abstract::$_externalFields
     * @return array Mock up data.
     */
    protected function _fetchMultiField() {
        return $this->_multiFieldValue;
    }

    /**
     * Mock function.
     *
     * @param Mixed $value Whatever data.
     * @see    Opus_Model_Abstract::$_externalFields
     * @return void
     */
    protected function _storeMultiModel($value) {
    }

    /**
     * Return an array with mock model identifiers.
     *
     * @see    Opus_Model_Abstract::$_externalFields
     * @return array Mock up identifiers.
     */
    protected function _fetchMultiModel() {
        return array();
    }


}
