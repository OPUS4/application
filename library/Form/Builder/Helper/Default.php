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
 * @category    Server
 * @author      Jens Schwidder <schwidder(at)zib.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Form_Builder_Helper_Default extends Form_Builder_Helper_Abstract {

    /**
     * Returns a subform for a model.
     *
     * @param  Opus_Model_Abstract  $model The model to render a subform for.
     * @return Zend_Form_SubForm The subform for the model.
     */
    public function buildModelForm(Opus_Model_Abstract $model) {
        // Construct subform to hold elements.
        $subForm = new Zend_Form_SubForm();
        $subForm->removeDecorator('DtDdWrapper');
        $subForm->setLegend(get_class($model));

        // Add hidden model Id when necessary
        if ($model instanceof Opus_Model_AbstractDb) {
            $idElement = $this->createModelIdElement($model->getId());
            $subForm->addElement($idElement);
        }

        $this->processFields($model, $subForm);

        $this->_addDescription(get_class($model) . '_form', $subForm);
        $subForm->removeDecorator('Fieldset');
        return $subForm;
    }

    protected function processFields($model, $subForm) {
        // Iterate over fields and build a subform for each field.
        foreach ($model->describe() as $i => $fieldName) {
            $field = $model->getField($fieldName);
            $helper = new Form_Builder_Helper_Default();
            $fieldForm = $helper->buildFieldForm($field, get_class($model));
            $subForm->addSubForm($fieldForm, $fieldName);
        }
    }

    protected function createModelIdElement($id) {
        $idElement = new Zend_Form_Element_Hidden('Id');
        $idElement->removeDecorator('HtmlTag');
        $idElement->removeDecorator('Label');
        $idElement->removeDecorator('DtDdWrapper');
        $idElement->setAttrib('class', 'identifier');
        if (true === is_array($id)) $id = implode(',', $id);
        $idElement->setValue($id);
        return $idElement;
    }

    /**
     * Returns a subform for a field.
     *
     * @param  Opus_Model_Field  $field The field to render a subform for.
     * @return Zend_Form_SubForm The subform for the field.
     */
    public function buildFieldForm(Opus_Model_Field $field, $modelName = '') {
        // Get field properties.
        $fieldName = $field->getName();
        $valueModelClass = $field->getValueModelClass();

        $fieldValues = $this->_getFieldValues($field);

        // Iterate over values, placing them on the appropriate subform.
        $fieldForm = new Zend_Form_SubForm;
        $fieldForm->removeDecorator('HtmlTag');
        $fieldForm->removeDecorator('DtDdWrapper');
        $fieldForm->setLegend($fieldName);

        if (empty($valueModelClass) !== true) {
            // add an empty element to allow help- and hint texts for whole subfields
            $helpElement = new Zend_Form_Element_Hidden('nothing');
            $helpElement->setAttrib('class', 'hiddenelement');
            $this->_addDescription( $modelName . '_' . $fieldName . '_field', $helpElement);
            $fieldForm->addElement($helpElement);
        }

        if (false === empty($fieldValues)) {
            foreach ($fieldValues as $i => $fieldValue) {
                if ($fieldValue instanceof Opus_File) {
                    $fileInput = $this->_createFileField($fieldName, $modelName);
                    $fieldForm->addElement($fileInput);
                }
                else if ($fieldValue instanceof Opus_Model_Abstract) {
                    if ($field->isSelection()) {
                        $this->_handleSelectionField($fieldForm, $i, $field, $fieldValue, $modelName);
                    }
                    else {
                        // If value is not a selection of models, embed subform
                        // FIXME: hardcoded check to deal with infinite
                        // recursion in collection fields
                        if ($fieldValue instanceof Opus_Collection) {
                            continue;
                        }

                        $helper = $this->getModelFormBuilder($fieldValue);

                        $fieldForm->addSubForm($helper->buildModelForm($fieldValue), $i + 1);

                        $this->_addDescription($modelName . '_' . $fieldName, $fieldForm);
                    }
                }
                else if (true === is_null($valueModelClass)) {
                    $this->_handleSimpleField($fieldForm, $i, $field, $fieldValue, $modelName);
                }

                // Create button to remove value when appropriate.
                if (1 < count($fieldValues) or (false === $field->isMandatory() and false === is_null($valueModelClass))) {
                    $element = new Zend_Form_Element_Submit('remove_' . strVal($i + 1));
                    $element->removeDecorator('DtDdWrapper');
                    $element->setBelongsTo('Actions');
                    $element->setLabel('remove_' . $fieldName);
                    $element->setAttrib('class', 'button remove');
                    $this->_addDescription($modelName . '_' . $fieldName, $element);
                    $fieldForm->addElement($element);
                }
            }
        }

        $this->_handleMultiplicity($fieldForm, $field, $fieldValues, $modelName);

        $fieldForm->setAttrib('class', $valueModelClass);
        return $fieldForm;
    }

    protected function _getFieldValues($field) {
        $fieldValues = $field->getValue();

        $valueModelClass = $field->getValueModelClass();
        $linkModelClass = $field->getLinkModelClass();

        // If getter returns no value, initialize new values if field is
        // mandatory, since null-values will be ignored.
        if (true === empty($fieldValues) and (true === $field->isMandatory())) {
            if (false === is_null($linkModelClass)) {
                $fieldValues = new $linkModelClass;
                $target = new $valueModelClass;
                $fieldValues->setModel($target);
            }
            else if (false === is_null($valueModelClass)) {
                $fieldValues = new $valueModelClass;
            }
            else {
                $fieldValues = '';
            }
        }
        else if (true === empty($fieldValues) and (true === is_null($field->getValueModelClass()))) {
            $fieldValues = '';
        }

        // Construct value array if neccessary
        if (false === is_array($fieldValues) and false === is_null($fieldValues)) {
            $fieldValues = array($fieldValues);
        }

        return $fieldValues;
    }

    protected function _handleMultiplicity($fieldForm, $field, $fieldValues, $modelName) {
        $this->log->info('Handle multiplicity for ' . $modelName);
        $this->log->info('fieldValueCount ' . count($fieldValues));
        $fieldName = $field->getName();
        $this->log->info('fieldName = ' . $fieldName);
        $this->log->info('fieldMultiplicity = ' . $field->getMultiplicity());
        // Create button to add value when appropriate.
        if (count($fieldValues) < $field->getMultiplicity() or '*' === $field->getMultiplicity()) {
            // add an empty element to allow help- and hint texts for whole subfields
            $helpElement = new Zend_Form_Element_Hidden('nothing');
            $helpElement->setAttrib('class', 'hiddenelement');
            $this->_addDescription($modelName . '_' . $fieldName . '_field', $helpElement);
            $fieldForm->addElement($helpElement);

            $element = new Zend_Form_Element_Submit('add');
            $element->removeDecorator('DtDdWrapper');
            $element->setBelongsTo('Actions');
            $element->setLabel('add_' . $fieldName);
            $element->setAttrib('name', 'Action');
            $this->_addDescription($modelName . '_' . $fieldName . '_add', $element);
            $element->setAttrib('class', 'description');
            $element->setAttrib('class', 'button add');
            $fieldForm->addElement($element);
        }
    }

    protected function _handleSelectionField($fieldForm, $i, $field, $fieldValue, $modelName) {
        // If value is a selection of models, build selection widget
        $fieldName = $field->getName();

        $widget = new Zend_Form_Element_Select(strVal($i + 1));
        $widget->setRequired($field->isMandatory());

        $message = Zend_Registry::get('Zend_Translate')->_('choose_option') . ' ' . Zend_Registry::get('Zend_Translate')->_($fieldName);
        $widget->addMultiOption('', $message);

        $options = $field->getDefault();
        foreach ($options as $option) {
            $widget->addMultiOption($option->getId(), $option->getDisplayName());
        }

        $widget->setValue($fieldValue->getId());
        $widget->getDecorator('Label')->setTag(null);
        $widget->removeDecorator('HtmlTag');
        $widget->setAttrib('class', $fieldName);
        $this->_addDescription($modelName . '_' . $fieldName, $widget);
        $fieldForm->addElement($widget);
    }

    protected function _createFileField($fieldName, $modelName) {
        // Hardcoded class name for file inputs!
        $fileInput = new Zend_Form_Element_File($fieldName);
        $fileInput->setLabel($fieldName);
        $this->_addDescription($modelName . '_' . $fieldName, $fileInput);
        return $fileInput;
    }

    protected function _handleSimpleField($fieldForm, $i, $field, $fieldValue, $modelName) {
        $fieldName = $field->getName();
        $validator = $field->getValidator();
        // If value is simple, build corresponding widget
        if ($field->isTextarea()) {
            $widget = new Zend_Form_Element_Textarea(strVal($i + 1));
        }
        else if ($field->isCheckbox()) {
            $widget = new Zend_Form_Element_Checkbox(strVal($i + 1));
        }
        else if ($field->isSelection()) {
            $options = $field->getDefault();
            $widget = new Zend_Form_Element_Select(strVal($i + 1));
            $message = Zend_Registry::get('Zend_Translate')->_('choose_option') . ' ' . Zend_Registry::get('Zend_Translate')->_($fieldName);
            $widget->addMultiOption('', $message);
            foreach ($field->getDefault() as $key => $option) {
                $widget->addMultiOption($key, $option);
            }
        }
        else {
            $widget = new Zend_Form_Element_Text(strVal($i + 1));
        }
        $widget->getDecorator('Label')->setOption('tag','div');

        $widget->removeDecorator('HtmlTag');
        $widget->setValue($fieldValue);
        $widget->setLabel($fieldName);
        $widget->setRequired($field->isMandatory());

        $this->_addDescription($modelName . '_' . $fieldName, $widget);
        if (false === is_null($validator)) {
            $widget->addValidator($validator);
        }
        $widget->setAttrib('class', $fieldName);
        $fieldForm->addElement($widget);
        $fieldForm->removeDecorator('Fieldset');
    }

    public function getModelFormBuilder($model) {
        if ($model instanceof Opus_Date) {
            return new Form_Builder_Helper_Date();
        }
        else {
            return new Form_Builder_Helper_Default();
        }
    }

}

?>
