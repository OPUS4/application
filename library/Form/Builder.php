<?php

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
        // Construct subform for model
        $subForm = $this->__buildModelForm($model);
        $form->addSubForm($subForm, get_class($model));
        // Add submit button to form
        $element = new Zend_Form_Element_Submit('submit');
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
     * CAUTION: This method has side effects!
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
                $clazz = $field->getValueModelClass();
                $fieldValues = array();
                foreach ($values as $key => $value) {
                    if (true === $field->isSelection()) {
                        // A selection is a shortcut to an existing model.
                        if ('add' === $key) {
                            $fieldValues[] = new $clazz();
                        } else {
                            $fieldValues[] = new $clazz($value);
                        }
                    } else if ('add' !== $key) {
                        // The 'add' key is reserved for adding a new (blank) subform.
                        if (false === array_key_exists('Id', $value) or '' === $value['Id']) {
                            $id = null;
                        } else {
                            $id = $value['Id'];
                            unset($value['Id']);
                        }
                        $fieldValue = new $clazz($id);
                        $this->__populateModel($fieldValue, $value);
                        $fieldValues[] = $fieldValue;
                    } else {
                        // Create a new model for the value
                        $fieldValues[] = new $clazz;
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
        $subForm->setLegend(get_class($model));

        // Add Id when necessary
        if ($model instanceof Opus_Model_AbstractDb) {
            $idElement = new Zend_Form_Element_Hidden('Id');
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
        $fieldValues = $field->getValue();

        // If getter returns no value, initialize new values if field is
        // mandatory, since null-values will be ignored.
        if (true === empty($fieldValues) and (true === $field->isMandatory())) {
            if (false === is_null($valueModelClass)) {
                $fieldValues = new $valueModelClass;
            } else {
                $fieldValues = '';
            }
        }

        // Construct value array if neccessary
        if (false === is_array($fieldValues) and false === is_null($fieldValues)) {
            $fieldValues = array($fieldValues);
        }

        // Iterate over values, placing them on the appropriate subform.
        $fieldForm = new Zend_Form_SubForm;
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
                        foreach ($options as $option) {
                            $widget->addMultiOption($option->getId(), $option->getDisplayName());
                        }
                        $fieldForm->addElement($widget);
                        $widget->setValue($fieldValue->getId());
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
                        $widget->setMultiOptions($options);
                    } else {
                        $widget = new Zend_Form_Element_Text(strVal($i + 1));
                    }
                    $widget->setValue($fieldValue);
                    $widget->setAttrib('class', $fieldName);
                    $widget->setRequired($mandatory);
                    if (false === is_null($validator)) {
                        $widget->addValidator($validator);
                    }
                    $fieldForm->addElement($widget);
                }
                // Create button to remove value when appropriate.
                if (1 < count($fieldValues) or false === $field->isMandatory()) {
                    $element = new Zend_Form_Element_Submit('remove_' . strVal($i + 1));
                    $element->setBelongsTo('Actions');
                    $element->setLabel('remove_' . $fieldName);
                    $fieldForm->addElement($element);
                }
            }
        }

        // Create button to add value when appropriate.
        if (count($fieldValues) < $field->getMultiplicity() or '*' === $field->getMultiplicity()) {
            $element = new Zend_Form_Element_Submit('add');
            $element->setBelongsTo('Actions');
            $element->setLabel('add_' . $fieldName);
            $element->setAttrib('name', 'Action');
            $fieldForm->addElement($element);
        }

        return $fieldForm;
    }

}
