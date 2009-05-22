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
 * @package     Form
 * @author      Ralf Claussnitzer <ralf.claussnitzer@slub-dresden.de>
 * @author      Henning Gerhardt <henning.gerhardt@slub-dresden.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Methods to builds a Zend_Form from an Opus_Model_* class.
 *
 * @category    Application
 * @package     Form
 *
 */
class Form_Builder {

    /**
     * Name of the form element that contains the serialized model.
     *
     */
    const HIDDEN_MODEL_ELEMENT_NAME = '__model';

    /**
     * Build an Zend_Form object from a given model. The generated form object
     * containes Zend_Form_Elements for each field of the document. If a
     * document field refers to another model instance then a sub form is
     * created.
     *
     * Additionally the given model object is serialized, compressed and base64
     * encoded and stored in a hidden form field "__model".
     *
     * @param Opus_Model_Abstract $model         Model to build a form for.
     * @param boolean             $createSubForm (Optional) True, if a sub form should be
     *                                           generated instead of a form.
     * @return Zend_Form The generated form object.
     */
    public function build(Opus_Model_Abstract $model, $createSubForm = false) {
        if ($createSubForm === true) {
            $form = new Zend_Form_SubForm();
        } else {
            $form = new Zend_Form();
        }

        foreach ($model->describe() as $fieldname) {
            $field = $model->getField($fieldname);
            $this->_prepareElement($field, $form);
        }

        if ($createSubForm === false) {
            $element = new Zend_Form_Element_Hidden(self::HIDDEN_MODEL_ELEMENT_NAME);
            $element->setValue($this->compressModel($model));
            $form->addElement($element);

            $element = new Zend_Form_Element_Submit('submit');
            $element->setLabel('transmit');
            $form->addElement($element);
        }

        return $form;
    }

    /**
     * Use form post data to recreate the form and update the serialized model.
     *
     * @param array $post Form post data as sent back from the browser.
     * @return Zend_Form The recreated and updated form object.
     */
    public function buildFromPost(array $post) {
        $modelelementname = self::HIDDEN_MODEL_ELEMENT_NAME;
        $model = $this->uncompressModel($post[$modelelementname]);

        $this->_addRemove($post);
        $this->setFromPost($model, $post);

        $form = $this->build($model);
        $form->$modelelementname->setValue($this->compressModel($model));

        return $form;
    }

    /**
     * Compress a model object for transfering in forms.
     *
     * @param Opus_Model_Abstract $model Model object to compress
     * @return string
     */
    public function compressModel(Opus_Model_Abstract $model) {
        return base64_encode(bzcompress(serialize($model)));
    }

    /**
     * Returns model from given form.
     *
     * @param Zend_Form $form Form object with compact model information
     * @return Opus_Document|null Returns an Opus_Document or
     *                                  null if no model information are in form
     */
    public function getModelFromForm(Zend_Form $form) {
        $model = null;
        $modelelementname = self::HIDDEN_MODEL_ELEMENT_NAME;
        $modelelement = $form->getElement($modelelementname);
        if (is_null($modelelement) === false) {
            $model = $this->uncompressModel($modelelement->getValue());
        }
        return $model;
    }

    /**
     * Set values from post data into model.
     *
     * @param Opus_Model_Abstract $model Model
     * @param array               $post  Post
     * @return void
     */
    public function setFromPost(Opus_Model_Abstract $model, array $post) {
        foreach ($post as $fieldname => $value) {
            $field = $model->getField($fieldname);
            // set only field which exists in model
            if (true === is_null($field)) {
                continue;
            }

            $this->_setPostData($field, $value, $model);
        }
    }

    /**
     * Uncompress a compressed model object.
     *
     * @param string $model Compressed model object.
     * @throws Opus_Form_Exception Thrown if compressed model data are invalid.
     * @return Opus_Model_Abstract
     */
    public function uncompressModel($model) {
        try {
            $result = unserialize(bzdecompress(base64_decode($model)));
        } catch (Exception $e) {
            throw new Opus_Form_Exception('Model data are not unserializable.');
        }
        return $result;
    }

    /**
     * Adds a description if a translation is available.
     *
     * @param Opus_Model_Field $field Field with description information.
     * @param Zend_Form        $form  Form container where the field should be exists.
     * @return void
     */
    protected function _addDescription(Opus_Model_Field $field, Zend_Form $form) {
        $fieldname = $field->getName();
        $descrfield = $fieldname . 'Descr';
        $trans = $form->getTranslator()->_($descrfield);
        $element = $form->getElement($fieldname);
        if (($trans !== $descrfield) and ($element instanceof Zend_Form_Element)) {
            // set decorator - maybe only needed for ZF 1.6.x
            $element->addDecorators(array(
            array('Description', array('tag' => 'p', 'class' => 'description')),
            ));
            $element->setDescription($trans);
        }
    }

    /**
     * Add a given filter to form element.
     *
     * @param Opus_Model_Field $field Field object with necessary field informations
     * @param Zend_Form        $form  Form object which filter should be added
     * @return void
     */
    protected function _addFilter(Opus_Model_Field $field, Zend_Form $form) {
        $fieldname = $field->getName();
        $filter = $field->getFilter();
        if ((empty($filter) === false) and ($form->$fieldname instanceof Zend_Form_Element)) {
            $form->$fieldname->addFilter($filter);
        }
    }

    /**
     * Add a required attribute to proper fields
     *
     * @param Opus_Model_Field $field Field object with necessary field informations
     * @param Zend_Form        $form  Form object which validator should be added
     * @return void
     */
    protected function _addMandatory(Opus_Model_Field $field, Zend_Form $form) {
        $fieldname = $field->getName();
        $mandatory = $field->isMandatory();
        if ($form->$fieldname instanceof Zend_Form_Element) {
            $form->$fieldname->setRequired($mandatory);
        }
    }

    /**
     * Search for an action (add or remove) and do this action.
     *
     * @param array &$haystack Where to search
     * @return array|null Null is returned if nothing is found else a path list
     */
    protected function _addRemove(array &$haystack) {
        $result = null;
        foreach ($haystack as $a_key => &$a_value) {
            if (preg_match('/^(add|remove)_(.*)/', $a_key) === 1) {
                $result = $a_key;
            }
            if (is_array($a_value) === true) {
                $ref = $this->_addRemove($a_value);
                if (is_null($ref) === false) {
                    $this->__addRemoveAction($ref, $a_value);
                }
            }
        }
        return $result;
    }

    /**
     * Add a validator or a chain of validators to a Zend_Form field
     *
     * @param Opus_Model_Field $field Field object with necessary field informations
     * @param Zend_Form        $form  Form object which validator should be added
     * @return void
     */
    protected function _addValidator(Opus_Model_Field $field, Zend_Form $form) {
        $fieldname = $field->getName();
        $validator = $field->getValidator();
        if ((is_string($validator) === true) or ($validator instanceOf Zend_Validate_Interface)) {
            $form->$fieldname->addValidator($validator);
        }
    }

    /**
     * Build a checkbox element.
     *
     * @param Opus_Model_Field $field     Field object with building informations.
     * @param Zend_Form        $container Zend_Form object to add created element to.
     * @return void
     */
    protected function _makeCheckboxElement(Opus_Model_Field $field, Zend_Form $container) {
        $fieldname = $field->getName();
        $element = new Zend_Form_Element_Checkbox($fieldname);
        $element->setLabel($fieldname);
        $element->setValue($field->getValue());
        $container->addElement($element);
        $this->_setFieldAttributes($field, $container);
    }

    /**
     * Create a multiple general element (default single input line).
     *
     * @param Opus_Model_Field $field     Holds field informations.
     * @param Zend_Form        $container Container where to add this field.
     * @return void
     */
    protected function _makeMultiElement(Opus_Model_Field $field, Zend_Form $container) {
        $fieldname = $field->getName();
        $count = count($field->getValue());
        $subform = new Zend_Form_SubForm();
        $subform->setLegend($fieldname);
        $i = 1;
        $values = $field->getValue();
        do {
            // every element must be holded in a subform
            $helpform = new Zend_Form_Subform();
            $helpform->setLegend((string) $i);
            // clone field and set current value
            $clone_field = clone $field;
            $clone_field->setMultiplicity(1);
            $clone_field->setValue(array_shift($values));
            $this->_makeTextElement($clone_field, $helpform);
            $subform->addSubForm($helpform, (string) $i);
            $this->__addRemoveButton($field, $subform, $i);
            $i++;
        } while ($i <= $count);
        $this->__addAddButton($field, $subform);
        $container->addSubForm($subform, $fieldname);
    }

    /**
     * Create a multiple checkbox element.
     *
     * @param Opus_Model_Field $field     Holds field informations.
     * @param Zend_Form        $container Container where to add this field.
     * @return void
     */
    protected function _makeMultiElementCheckbox(Opus_Model_Field $field, Zend_Form $container) {
        $fieldname = $field->getName();
        $count = count($field->getValue());
        $subform = new Zend_Form_SubForm();
        $subform->setLegend($fieldname);
        $i = 1;
        $values = $field->getValue();
        do {
            // every element must be holded in a subform
            $helpform = new Zend_Form_Subform();
            $helpform->setLegend((string) $i);
            // clone field and set current value
            $clone_field = clone $field;
            $clone_field->setMultiplicity(1);
            $clone_field->setValue(array_shift($values));
            $this->_makeCheckboxElement($clone_field, $helpform);
            $subform->addSubForm($helpform, (string) $i);
            $this->__addRemoveButton($field, $subform, $i);
            $i++;
        } while ($i <= $count);
        $this->__addAddButton($field, $subform);
        $container->addSubForm($subform, $fieldname);
    }

    /**
     * Create a multiple selection element.
     *
     * @param Opus_Model_Field $field     Holds field informations.
     * @param Zend_Form        $container Container where to add this field.
     * @return void
     */
    protected function _makeMultiElementSelection(Opus_Model_Field $field, Zend_Form $container) {
        $fieldname = $field->getName();
        $count = count($field->getValue());
        $subform = new Zend_Form_SubForm();
        $subform->setLegend($fieldname);
        $i = 1;
        $values = $field->getValue();
        do {
            // every element must be holded in a subform
            $helpform = new Zend_Form_Subform();
            $helpform->setLegend((string) $i);
            // clone field and set current value
            $clone_field = clone $field;
            $clone_field->setMultiplicity(1);
            $clone_field->setValue(array_shift($values));
            $this->_makeSelectionElement($clone_field, $helpform);
            $subform->addSubForm($helpform, (string) $i);
            $this->__addRemoveButton($field, $subform, $i);
            $i++;
        } while ($i <= $count);
        $this->__addAddButton($field, $subform);
        $container->addSubForm($subform, $fieldname);
    }

    /**
     * Create a multiple textarea element.
     *
     * @param Opus_Model_Field $field     Holds field informations.
     * @param Zend_Form        $container Container where to add this field.
     * @return void
     */
    protected function _makeMultiElementTextarea(Opus_Model_Field $field, Zend_Form $container) {
        $fieldname = $field->getName();
        $count = count($field->getValue());
        $subform = new Zend_Form_SubForm();
        $subform->setLegend($fieldname);
        $i = 1;
        $values = $field->getValue();
        do {
            // every element must be holded in a subform
            $helpform = new Zend_Form_Subform();
            $helpform->setLegend((string) $i);
            // clone field and set current value
            $clone_field = clone $field;
            $clone_field->setMultiplicity(1);
            $clone_field->setValue(array_shift($values));
            $this->_makeTextAreaElement($clone_field, $helpform);
            $subform->addSubForm($helpform, (string) $i);
            $this->__addRemoveButton($field, $subform, $i);
            $i++;
        } while ($i <= $count);
        $this->__addAddButton($field, $subform);
        $container->addSubForm($subform, $fieldname);
    }

    /**
     * Create a multi model.
     *
     * @param Opus_Model_Field $field     Field to create.
     * @param Zend_Form        $container Container where to add field.
     * @return void
     */
    protected function _makeMultiModel(Opus_Model_Field $field, Zend_Form $container) {
        $fieldname = $field->getName();
        $count = count($field->getValue());
        $subform = new Zend_Form_SubForm();
        $subform->setLegend($fieldname);
        $i = 1;
        if (0 === $count) {
            $modelClassName = $field->getValueModelClass();
            $this->_makeSubForm($i, new $modelClassName, $subform);
        } else {
            foreach ($field->getValue() as $fieldvalue) {
                // fieldvalue contains a "working" model
                $this->_makeSubForm((string) $i, $fieldvalue, $subform);
                $this->__addRemoveButton($field, $subform, (string) $i);
                $i++;
            }
        }
        $this->__addAddButton($field, $subform);
        $container->addSubForm($subform, $fieldname);
    }

    /**
     * Build a selection element.
     *
     * @param Opus_Model_Field $field     Field object with building informations.
     * @param Zend_Form        $container Zend_Form object to add created element to.
     * @return void
     */
    protected function _makeSelectionElement(Opus_Model_Field $field, Zend_Form $container) {
        $fieldname = $field->getName();
        $element = new Zend_Form_Element_Select($fieldname);
        $element->setLabel($fieldname);
        $defaults = $field->getDefault();
        $value = $field->getValue();
        if ($value instanceOf Opus_Model_Dependent_Link_Abstract) {
            $preselect = $value->getLinkedModelId();
        } else if ($value instanceOf Opus_Model_Abstract) {
            $preselect = $value->getId();
        } else {
            $preselect = $value;
        }
        foreach ($defaults as $key => $default) {
            if ($default instanceOf Opus_Model_Abstract) {
                if ($default->getId() === $preselect) {
                    $preselect = $key;
                }
                $value = $default->getDisplayName();
                $element->addMultiOption($key, $value);
            } else {
                $element->addMultiOption($key, $default);
            }
        }
        $element->setValue($preselect);
        $container->addElement($element);
        $this->_setFieldAttributes($field, $container);
    }

    /**
     * Made a singe model.
     *
     * @param Opus_Model_Field $field     Field to create.
     * @param Zend_Form        $container Container where to add field.
     * @return void
     */
    protected function _makeSingleModel(Opus_Model_Field $field, Zend_Form $container) {
        $fieldname = $field->getName();
        $count = count($field->getValue());
        if (0 === $count) {
            $modelClassName = $field->getValueModelClass();
            $this->_makeSubForm($fieldname, new $modelClassName, $container);
        } else {
            // field->getValue() holds model
            $this->_makeSubForm($fieldname, $field->getValue(), $container);
        }
    }

    /**
     * Build a sub form.
     *
     * @param string              $name      Name of the subform.
     * @param Opus_Model_Abstract $model     Model object with building informations.
     * @param Zend_Form           $container Zend_Form object to add created element to.
     * @return void
     */
    protected function _makeSubForm($name, Opus_Model_Abstract $model, Zend_Form $container) {
        $subform = $this->build($model, true);
        $subform->setLegend($name);
        $container->addSubForm($subform, $name);
    }

    /**
     * Build a textarea element.
     *
     * @param Opus_Model_Field $field     Field object with building informations.
     * @param Zend_Form        $container Zend_Form object to add created element to.
     * @return void
     */
    protected function _makeTextAreaElement(Opus_Model_Field $field, Zend_Form $container) {
        $fieldname = $field->getName();
        $element = new Zend_Form_Element_Textarea($fieldname);
        $element->setLabel($fieldname);
        $element->setValue($field->getValue());
        // TODO values should be configurable
        $element->setAttribs(array('rows' => 10, 'cols' => 60));
        $container->addElement($element);
        $this->_setFieldAttributes($field, $container);
    }

    /**
     * Build a text element.
     *
     * @param Opus_Model_Field $field     Field object with building informations.
     * @param Zend_Form        $container Zend_Form object to add created element to.
     * @return void
     */
    protected function _makeTextElement(Opus_Model_Field $field, Zend_Form $container) {
        $fieldname = $field->getName();
        $element = new Zend_Form_Element_Text($fieldname);
        $element->setLabel($fieldname);
        $fieldvalue = $field->getValue();
        if ((is_array($fieldvalue) === true) or (is_object($fieldvalue) === true)) {
            // FIXME: Workaround for date fields
            if ($fieldvalue instanceOf Zend_Date) {
                $dateFormat = null;
                if (true === Zend_Registry::isRegistered('Zend_Translate')) {
                    $locale = Zend_Registry::get('Zend_Translate')->getLocale();
                    $date = new Zend_Date($fieldvalue, null, $locale);
                    $dateFormat = Zend_Locale_Format::getDateFormat($locale);
                }
                $fieldvalue = $date->toString($dateFormat);
            } else {
                $fieldvalue = '';
            }

        }
        $element->setValue($fieldvalue);
        $container->addElement($element);
        $this->_setFieldAttributes($field, $container);
    }

    /**
     * Prepare which kind of element should be created.
     *
     * @param Opus_Model_Field $field     Field
     * @param Zend_Form        $container ZendForm
     * @throws Opus_Form_Exception Thrown on error
     * @return void
     */
    protected function _prepareElement(Opus_Model_Field $field, Zend_Form $container) {

        $elementToCreate = $this->__determinateFieldAction($field);

        switch ($elementToCreate) {
            case 'SingleElement':
                $this->_makeTextElement($field, $container);
                break;

            case 'SingleElementCheckbox':
                $this->_makeCheckboxElement($field, $container);
                break;

            case 'SingleElementSelection':
                // Break intentionally omitted
            case 'SingleModelSelection':
                $this->_makeSelectionElement($field, $container);
                break;

            case 'SingleElementTextarea':
                $this->_makeTextAreaElement($field, $container);
                break;

            case 'SingleModel':
                // Break intentionally omitted
            case 'SingleModelCheckbox':
                // Break intentionally omitted
            case 'SingleModelTextarea':
                $this->_makeSingleModel($field, $container);
                break;

            case 'MultiElement':
                $this->_makeMultiElement($field, $container);
                break;

            case 'MultiElementCheckbox':
                $this->_makeMultiElementCheckbox($field, $container);
                break;

            case 'MultiElementSelection':
                // Break intentionally omitted
            case 'MultiModelSelection':
                $this->_makeMultiElementSelection($field, $container);
                break;

            case 'MultiElementTextarea':
                $this->_makeMultiElementTextarea($field, $container);
                break;

            case 'MultiModel':
                // Break intentionally omitted
            case 'MultiModelCheckbox':
                // Break intentionally omitted
            case 'MultiModelTextarea':
                $this->_makeMultiModel($field, $container);
                break;

            default:
                throw new Opus_Form_Exception('No action found for field "' . $field->getName() . '"');
                break;
        }

    }

    /**
     * Set field attributes.
     *
     * @param Opus_Model_Field $field Field with necessary attribute information.
     * @param Zend_Form        $form  Form where field attributes are to be set.
     * @return void
     */
    protected function _setFieldAttributes(Opus_Model_Field $field, Zend_Form $form) {
        // set element attributes
        $this->_addDescription($field, $form);
        $this->_addFilter($field, $form);
        $this->_addMandatory($field, $form);
        $this->_addValidator($field, $form);
    }

    /**
     * Set values of a multiple element.
     *
     * @param Opus_Model_Field $field      Field for setting values.
     * @param mixed            &$postvalue Contains field values.
     * @return void
     */
    protected function _setMultiElement(Opus_Model_Field $field, &$postvalue) {
        $result = array();
        foreach ($postvalue as $key => $value) {
            // skip non-numeric key for example remove buttons
            if (false === is_numeric($key)) {
                continue;
            }
            if (true === is_array($value)) {
                $vals = current(array_values($value));
            } else {
                $vals = $value;
            }
            $result[] = $vals;
        }
        $field->setValue($result);
    }

    /**
     * Set values of a multiple model.
     *
     * @param Opus_Model_Field $field      Field for setting values.
     * @param mixed            &$postvalue Contains field values.
     * @return void
     */
    protected function _setMultiModel(Opus_Model_Field $field, &$postvalue) {

        $result = array();
        $modelclass = $field->getValueModelClass();
        $linkclass = $field->getLinkModelClass();

        foreach ($postvalue as $key => $value) {
            // skip non-numeric key for example remove buttons
            if (false === is_numeric($key)) {
                continue;
            }
            if (false === is_null($linkclass)) {
                $linkmodel = new $linkclass;
                $submodel = new $modelclass;
                $linkmodel->setModel($submodel);
                $submodel = $linkmodel;
            } else {
                $submodel = new $modelclass;
            }
            if (false === is_array($value)) {
                $value = array($value);
            }

            $this->setFromPost($submodel, $value);
            $result[] = $submodel;
        }
        $field->setValue($result);
    }

    /**
     * Set multiple selection models.
     *
     * @param Opus_Model_Field $field      Field for setting values.
     * @param mixed            &$postvalue Contains field values.
     * @return void
     */
    protected function _setMultiModelSelection(Opus_Model_Field $field, &$postvalue) {

        $result = array();
        $modelclass = $field->getValueModelClass();
        $linkclass = $field->getLinkModelClass();
        $defaults = $field->getDefault();

        foreach ($postvalue as $key => $value) {
            // skip non-numeric key for example remove buttons
            if (false === is_numeric($key)) {
                continue;
            }
            if (true === is_array($value)) {
                $vals = current(array_values($value));
            } else {
                $vals = $value;
            }
            if ('' === $vals) {
                $vals = 0;
            }
            $model = $defaults[$vals];
            if (false === is_null($linkclass)) {
                $linkmodel = new $linkclass;
                $linkmodel->setModel($model);
                $model = $linkmodel;
            }
            $result[] = $model;
        }
        $field->setValue($result);
    }

    /**
     * Set post data into model.
     *
     * @param Opus_Model_Field    $field      Field
     * @param mixed               &$postvalue PostData
     * @param Opus_Model_Abstract $model      (Optional) If given the Models native setter and adder methods are used
     *                                        to set values to fields.
     * @throws Opus_Form_Exception Thrown if a action is requested which is not available.
     * @return void
     */
    protected function _setPostData(Opus_Model_Field $field, &$postvalue, Opus_Model_Abstract $model = null) {

        $elementToSave = $this->__determinateFieldAction($field);

        switch ($elementToSave) {

            case 'SingleElement':
                // Break intentionally omitted
            case 'SingleElementCheckbox':
                // Break intentionally omitted
            case 'SingleElementSelection':
                // Break intentionally omitted
            case 'SingleElementTextarea':
                // FIXME Workaround to set values for simple fields via $model->set...
                if (null === $model) {
                    $field->setValue($postvalue);
                } else {
                    $callname = 'set' . $field->getName();
                    $model->$callname($postvalue);
                }
                break;

            case 'SingleModelSelection':
                $this->_setSingleModelSelection($field, $postvalue);
                break;

            case 'SingleModel':
                // Break intentionally omitted
            case 'SingleModelCheckbox':
                // Break intentionally omitted
            case 'SingleModelTextarea':
                $this->_setSingleModel($field, $postvalue);
                break;

            case 'MultiElement':
                // Break intentionally omitted
            case 'MultiElementCheckbox':
                // Break intentionally omitted
            case 'MultiElementSelection':
                // Break intentionally omitted
            case 'MultiElementTextarea':
                $this->_setMultiElement($field, $postvalue);
                break;

            case 'MultiModelSelection':
                $this->_setMultiModelSelection($field, $postvalue);
                break;

            case 'MultiModel':
                // Break intentionally omitted
            case 'MultiModelCheckbox':
                // Break intentionally omitted
            case 'MultiModelTextarea':
                $this->_setMultiModel($field, $postvalue);
                break;

            default:
                throw new Opus_Form_Exception('No action found for field "' . $field->getName() . '"');
                break;
        }

    }

    /**
     * Set value for a model.
     *
     * @param Opus_Model_Field $field      Field for setting value.
     * @param mixed            &$postvalue Contains field value.
     * @return void
     */
    protected function _setSingleModel(Opus_Model_Field $field, &$postvalue) {

        $modelclass = $field->getValueModelClass();
        $linkclass = $field->getLinkModelClass();

        if (false === is_null($linkclass)) {
            $linkmodel = new $linkclass;
            $submodel = new $modelclass;
            $linkmodel->setModel($submodel);
            $submodel = $linkmodel;
        } else {
            $submodel = new $modelclass;
        }
        $this->setFromPost($submodel, $postvalue);
        $field->setValue($submodel);
    }

    /**
     * Set single selection model.
     *
     * @param Opus_Model_Field $field      Field for setting value.
     * @param mixed            &$postvalue Contains field value.
     * @return void
     */
    protected function _setSingleModelSelection(Opus_Model_Field $field, &$postvalue) {

        $modelclass = $field->getValueModelClass();
        $linkclass = $field->getLinkModelClass();

        $defaults = $field->getDefault();
        $model = $defaults[$postvalue];
        if (false === is_null($linkclass)) {
            $linkmodel = new $linkclass;
            $linkmodel->setModel($model);
            $model = $linkmodel;
        }
        $field->setValue($model);
    }

    /**
     * Add a + button to a form.
     *
     * @param Opus_Model_Field $field     Holds necessary field informations.
     * @param Zend_Form        $container Form where to add.
     * @return void
     */
    private function __addAddButton(Opus_Model_Field $field, Zend_Form $container) {
        $mult = $field->getMultiplicity();
        $counts = count($field->getValue());
        if (('*' === $mult) or ($counts < $mult)) {
            $fieldname = $field->getName();
            $addButton = new Zend_Form_Element_Submit('add_' . $fieldname);
            $addButton->setLabel('+');
            $container->addElement($addButton);
        }
    }

    /**
     * Alter post data array with proper action.
     *
     * @param string $ref    Contains action to perform
     * @param array  &$value Reference to post data array
     * @return void
     */
    private function __addRemoveAction($ref, array &$value) {
        // split action command
        $fname = explode('_', $ref);
        // action to do
        $action = $fname[0];
        // remove action expression
        unset($value[$ref]);
        switch($action) {
            case 'add':
                // add a new field
                $value[] = '';
                break;

            case 'remove':
                // remove field at position
                $index = (int) $fname[2];
                // protect removing nonexisting fields or emptying structure
                if ((array_key_exists($index, $value) === true)
                and (count($value) > 1)) {
                    unset($value[$index]);
                }
                break;

            default:
                // No action taken
                break;
        }
    }

    /**
     * Add a - button to a form.
     *
     * @param Opus_Model_Field $field     Holds necessary field informations.
     * @param Zend_Form        $container Form where to add.
     * @param mixed            $iterator  Iterator number of remove field.
     * @return void
     */
    private function __addRemoveButton(Opus_Model_Field $field, Zend_Form $container, $iterator) {
        $counts = count($field->getValue());
        if ($counts > 1) {
            $fieldname = $field->getName() . '_' . $iterator;
            $removeButton = new Zend_Form_Element_Submit('remove_' . $fieldname);
            $removeButton->setLabel('-');
            $container->addElement($removeButton);
        }
    }

    /**
     * Determinate which action of a field should later be done.
     *
     * @param Opus_Model_Field $field Field with neccessary informations.
     * @return string
     */
    private function __determinateFieldAction(Opus_Model_Field $field) {
        $model = (false === is_null($field->getValueModelClass()));
        $multiple = $field->hasMultipleValues();
        $selection = $field->isSelection();
        $checkbox = $field->isCheckbox();
        $textarea = $field->isTextarea();

        $action = '';

        if (true === $multiple) {
            $action .= 'Multi';
        } else {
            $action .= 'Single';
        }

        if (true === $model) {
            $action .= 'Model';
        } else {
            $action .= 'Element';
        }

        if (true === $selection) {
            $action .= 'Selection';
        } else if (true === $textarea) {
            $action .= 'Textarea';
        } else if (true === $checkbox) {
            $action .= 'Checkbox';
        }

        return $action;
    }
}
