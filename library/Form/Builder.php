<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @package     Opus_Form
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @copyright   Copyright (c) 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Form Builder.
 */
class Form_Builder {

    /**
     * Returns a form for a model by integrating all necessary subforms.
     *
     * @param  Opus_Model_Abstract  $model The model to render a form for.
     * @return Zend_Form_Form The form for the model.
     */
    public function build(Opus_Model_Abstract $model) {
        // Construct base form
        $form = new Zend_Form;
        $form->removeDecorator('DtDdWrapper');
        $form->removeDecorator('HtmlTag');
        // Construct subform for model
        $subForm = $this->__buildModelForm($model);
        $form->addSubForm($subForm, get_class($model));
        // Add submit button to form
        $element = new Zend_Form_Element_Submit('submit');
        $element->removeDecorator('DtDdWrapper');
        $element->setLabel('transmit');
        $form->addElement($element);
        return $form;
    }

    /**
     * Updates or builds a model based on form data
     *
     * @return void
     */
    public function buildModelFromPostData($model, $data) {
        $this->__populateModel($model, $data);
    }

    /**
     * Populates an Opus_Model from post data.
     *
     * CAUTION: This method has side effects on the model passed in!
     *
     * @param  Opus_Model_Abstract  $model The model to populate.
     * @param  array  $post  The array containing the model data.
     * @return void
     */
    private function __populateModel(Opus_Model_Abstract $model, array $data) {
        foreach ($data as $fieldName => $values) {
            $field = $model->getField($fieldName);
            // FIXME: Under what condition does this happen?
            if (null === $field) continue;

            // The 'remove' key triggers deletion of a field value.
            // FIXME: Apparently the assignment of Zend_Form_Elements to an individual
            // array (such as add[TitleMain] or remove[TitleMain][1]) currently does
            // not work. This is why there is now other way than carrying this
            // information along with the model data for now.
            foreach ($values as $key => $value) {
                if ('remove' === substr($key, 0, strpos($key, '_'))) {
                    $removeKey = substr($key, strpos($key, '_') + 1);
                    unset($values[$key]);
                    unset($values[$removeKey]);
                }
            }

            // Set the values of the model's fields.
            if (null === $field->getValueModelClass()) {
                // Set datatype property.
                $accessor = 'set' . $fieldName;
                foreach ($values as $key => $value) {
                    // The 'add' key is reserved for adding a new field.
                    if ('add' === $key) {
                        $values[$key] = '';
                    }
                }
                $model->$accessor($values);
            } else {
                // Set object property.
                $accessor = 'set' . $fieldName;
                $fieldValues = array();
                foreach ($values as $key => $value) {
                    if (true === $field->isSelection()) {
                        // A selection is a shortcut to an existing model.
                        $clazz = $field->getValueModelClass();
                        if ('add' === $key) {
                            $fieldValues[] = new $clazz();
                        } else if ('' !== $value) {
                            $fieldValues[] = new $clazz($value);
                        }
                    } else if ('add' !== $key) {
                        // The 'add' key is reserved for adding a new (blank) subform.
                        $clazz = $field->getValueModelClass();
                        if (false === array_key_exists('Id', $value) or '' === $value['Id']) {
                            $id = null;
                        } else {
                            $id = $value['Id'];
                            if (!(false === strpos($id, ','))) {
                                $id = explode(',', $id);
                                $clazz = $field->getLinkModelClass();
                            }
                            unset($value['Id']);
                        }
                        $fieldValue = new $clazz($id);
                        if (false === is_null($field->getLinkModelClass()) and is_null($id)) {
                            $linkModelClass = $field->getLinkModelClass();
                            $link = new $linkModelClass();
                            $link->setModel($fieldValue);
                            $fieldValue = $link;
                        }
                        $this->__populateModel($fieldValue, $value);
                        $fieldValues[] = $fieldValue;
                    } else {
                        // Create a new model for the value
                        $clazz = $field->getValueModelClass();
                        $fieldValue = new $clazz;
                        if (false === is_null($field->getLinkModelClass())) {
                            $linkModelClass = $field->getLinkModelClass();
                            $link = new $linkModelClass();
                            $link->setModel($fieldValue);
                            $fieldValue = $link;
                        }
                        $fieldValues[] = $fieldValue;
                    }
                }
                $model->$accessor($fieldValues);
            }
        }
    }

    /**
     * Returns a subform for a model.
     *
     * @param  Opus_Model_Abstract  $model The model to render a subform for.
     * @return Zend_Form_SubForm The subform for the model.
     */
    private function __buildModelForm(Opus_Model_Abstract $model) {
        // Construct subform to hold elements.
        $subForm = new Zend_Form_SubForm();
        $subForm->removeDecorator('DtDdWrapper');
        $subForm->getDecorator('HtmlTag')->setOption('tag','div');
        $subForm->getDecorator('HtmlTag')->setOption('class',get_class($model));
        $subForm->setLegend(get_class($model));

        // Add Id when necessary
        if ($model instanceof Opus_Model_AbstractDb) {
            $idElement = new Zend_Form_Element_Hidden('Id');
            $idElement->removeDecorator('HtmlTag');
            $idElement->removeDecorator('Label');
            $idElement->removeDecorator('DtDdWrapper');
            $id = $model->getId();
            if (true === is_array($id)) $id = implode(',', $id);
            $idElement->setValue($id);
            $subForm->addElement($idElement);
        }

        // Iterate over fields and build a subform for each field.
        foreach ($model->describe() as $i => $fieldName) {
            $field = $model->getField($fieldName);
            $fieldForm = $this->__buildFieldForm($field);
            $subForm->addSubForm($fieldForm, $fieldName);
        }

        $subForm->removeDecorator('Fieldset');
        return $subForm;

    }

    /**
     * Returns a subform for a field.
     *
     * @param  Opus_Model_Field  $field The field to render a subform for.
     * @return Zend_Form_SubForm The subform for the field.
     */
    private function __buildFieldForm(Opus_Model_Field $field) {

        // Get field properties.
        $fieldName = $field->getName();
        $mandatory = $field->isMandatory();
        $validator = $field->getValidator();
        $valueModelClass = $field->getValueModelClass();
        $linkModelClass = $field->getLinkModelClass();
        $fieldValues = $field->getValue();

        // If getter returns no value, initialize new values if field is
        // mandatory, since null-values will be ignored.
        if (true === empty($fieldValues) and (true === $field->isMandatory())) {
            if (false === is_null($linkModelClass)) {
                $fieldValues = new $linkModelClass;
                $target = new $valueModelClass;
                $fieldValues->setModel($target);
            } else if (false === is_null($valueModelClass)) {
                $fieldValues = new $valueModelClass;
            } else {
                $fieldValues = '';
            }
        } else if (true === empty($fieldValues) and (true === is_null($field->getValueModelClass()))) {
            $fieldValues = '';
        }

        // Construct value array if neccessary
        if (false === is_array($fieldValues) and false === is_null($fieldValues)) {
            $fieldValues = array($fieldValues);
        }

        // Iterate over values, placing them on the appropriate subform.
        $fieldForm = new Zend_Form_SubForm;
        $fieldForm->getDecorator('HtmlTag')->setoption('tag','div');
        $fieldForm->removeDecorator('DtDdWrapper');
        $fieldForm->setLegend($fieldName);
        if (false === empty($fieldValues)) {
            foreach ($fieldValues as $i => $fieldValue) {
                if ($fieldValue instanceof Opus_File) {
                    // Hardcoded class name for file inputs!
                    $fileInput = new Zend_Form_Element_File($fieldName);
                    $fileInput->setLabel($fieldName);
                    $fieldForm->addElement($fileInput);
                } else if ($fieldValue instanceof Opus_Model_Abstract) {
                    if ($field->isSelection()) {
                        // If value is a selection of models, build selection widget
                        $options = $field->getDefault();
                        $widget = new Zend_Form_Element_Select(strVal($i + 1));
                        $widget->setRequired($mandatory);
                        $widget->removeDecorator('DtDdWrapper');
                        $message = Zend_Registry::get('Zend_Translate')->_('choose_option') . ' ' . Zend_Registry::get('Zend_Translate')->_($fieldName);
                        $widget->addMultiOption('', $message);
                        foreach ($options as $option) {
                            $widget->addMultiOption($option->getId(), $option->getDisplayName());
                        }
                        $widget->setValue($fieldValue->getId());
                        $widget->getDecorator('Label')->setTag(null);
                        $widget->removeDecorator('HtmlTag');
                        $fieldForm->addElement($widget);
                    } else {
                        // If value is not a selection of models, embed subform
                        // FIXME: hardcoded check to deal with infinite
                        // recursion in collection fields
                        if ($fieldValue instanceof Opus_Collection) continue;
                        $fieldForm->addSubForm($this->__buildModelForm($fieldValue), $i + 1);
                    }
                } else if (true === is_null($valueModelClass)) {
                    // If value is simple, build corresponding widget
                    if ($field->isTextarea()) {
                        $widget = new Zend_Form_Element_Textarea(strVal($i + 1));
                    } else if ($field->isCheckbox()) {
                        $widget = new Zend_Form_Element_Checkbox(strVal($i + 1));
                    } else if ($field->isSelection()) {
                        $options = $field->getDefault();
                        $widget = new Zend_Form_Element_Select(strVal($i + 1));
                        $message = Zend_Registry::get('Zend_Translate')->_('choose_option') . ' ' . Zend_Registry::get('Zend_Translate')->_($fieldName);
                        $widget->addMultiOption('', $message);
                        foreach ($field->getDefault() as $key => $option) {
                            $widget->addMultiOption($key, $option);
                        }
                    } else {
                        $widget = new Zend_Form_Element_Text(strVal($i + 1));
                    }
                    $widget->getDecorator('Label')->setTag(null);
                    $widget->getDecorator('Label')->setOption('tag','div');
                    $widget->getDecorator('HtmlTag')->setOption('tag','div');
                    $widget->getDecorator('HtmlTag')->setOption('class', $fieldName);
                    $widget->setValue($fieldValue);
                    $widget->setLabel($fieldName);
                    $widget->setRequired($mandatory);
                    if (false === is_null($validator)) {
                        $widget->addValidator($validator);
                    }
                    $fieldForm->addElement($widget);
                    $fieldForm->removeDecorator('Fieldset');
                }
                // Create button to remove value when appropriate.
                if (1 < count($fieldValues) or (false === $field->isMandatory() and false === is_null($valueModelClass))) {
                    $element = new Zend_Form_Element_Submit('remove_' . strVal($i + 1));
                    $element->removeDecorator('DtDdWrapper');
                    $element->setBelongsTo('Actions');
                    $element->setLabel('remove_' . $fieldName);
                    $fieldForm->addElement($element);
                }
            }
        }

        // Create button to add value when appropriate.
        if (count($fieldValues) < $field->getMultiplicity() or '*' === $field->getMultiplicity()) {
            $element = new Zend_Form_Element_Submit('add');
            $element->removeDecorator('DtDdWrapper');
            $element->setBelongsTo('Actions');
            $element->setLabel('add_' . $fieldName);
            $element->setAttrib('name', 'Action');
            $fieldForm->addElement($element);
        }

        return $fieldForm;
    }

}
