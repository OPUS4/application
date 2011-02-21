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

    private $DEBUG = false;

    private $log;

    public function __construct() {
        $this->log = Zend_Registry::get('Zend_Log');
    }

    /**
     * Returns a form for a model by integrating all necessary subforms.
     *
     * @param  Opus_Model_Abstract  $model The model to render a form for.
     * @return Zend_Form_Form The form for the model.
     */
    public function build(Opus_Model_Abstract $model) {
        $config = Zend_Registry::get('Zend_Config');
		$this->DEBUG = (bool) $config->debug;
        
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
        if ($model instanceof Opus_Date) {
            $this->log->debug('populateModel: Opus_Date');
            
            $dateStr = $data['date'][1];
            $date = new Zend_Date($dateStr);
            $model->setZendDate($date);
            return;
        }
        foreach ($data as $fieldName => $values) {
            $this->log->debug('fieldName = ' . $fieldName);
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
                    switch ($fieldName) {
                        // FIXME hier muessten alle Felder aufgeführt werden, die
                        // in der Datenbank als NULLable markiert sind
                        // ansonsten wird im Falle von leeren Eingabefeldern in der
                        // Datenbank der leere String gespeichert und damit z. T.
                        // der vorgegebene Wert NULL überschrieben
                        // insbesondere bei Feldern vom Typ year problematisch, da
                        // hier das Speichern von '' zum Wert 0000 führt (siehe MySQL Doku)
                        case 'PublishedYear' :
                        case 'PageFirst':
                        case 'PageLast':
                        case 'PageNumber':
                            if (empty($value)) {
                                $values[$key] = null;
                            }
                            break;
                    }
                }
                $this->log->debug('set value of ' . $fieldName . ' to ' . $values);
                $model->$accessor($values);
            }
            else {
                // Set object property.
                $accessor = 'set' . $fieldName;
                $fieldValues = array();
                foreach ($values as $key => $value) {
                    if (true === $field->isSelection()) {
                        // A selection is a shortcut to an existing model.
                        $clazz = $field->getValueModelClass();
                        if ('add' === $key) {
                            $fieldValues[] = new $clazz();
                        }
                        else if ('' !== $value) {
                            $fieldValues[] = new $clazz($value);
                        }
                    }
                    else if ('add' !== $key && 'nothing' !== $key) {
                        // The 'add' key is reserved for adding a new (blank) subform.
                        // The 'nothing' key should always be ignored
                        $clazz = $field->getValueModelClass();
                        if (false === array_key_exists('Id', $value) or '' === $value['Id']) {
                            $id = null;
                        }
                        else {
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
                    }
                    else if ('nothing' !== $key) {
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
        $subForm->setLegend(get_class($model));

        // Add Id when necessary
        if ($model instanceof Opus_Model_AbstractDb) {
            $idElement = new Zend_Form_Element_Hidden('Id');
            $idElement->removeDecorator('HtmlTag');
            $idElement->removeDecorator('Label');
            $idElement->removeDecorator('DtDdWrapper');
            $idElement->setAttrib('class', 'identifier');
            $id = $model->getId();
            if (true === is_array($id)) $id = implode(',', $id);
            $idElement->setValue($id);
            $subForm->addElement($idElement);
        }

        if ($model instanceof Opus_Date) {
            $fieldForm = $this->buildDateForm($model);
            $subForm->addSubForm($fieldForm, 'date');
        }
        else {
            // Iterate over fields and build a subform for each field.
            foreach ($model->describe() as $i => $fieldName) {
                $field = $model->getField($fieldName);
                $fieldForm = $this->__buildFieldForm($field, get_class($model));
                $subForm->addSubForm($fieldForm, $fieldName);
            }
        }

        $this->__addDescription(get_class($model) . '_form', $subForm);
        $subForm->removeDecorator('Fieldset');
        return $subForm;

    }

    private function buildDateForm($model) {
        $fieldName = 'date';

        $fieldForm = new Zend_Form_SubForm;
        $fieldForm->removeDecorator('HtmlTag');
        $fieldForm->removeDecorator('DtDdWrapper');
        $fieldForm->setLegend($fieldName);

        $widget = new Zend_Form_Element_Text(strVal(1));
        $widget->getDecorator('Label')->setOption('tag','div');
        $widget->removeDecorator('HtmlTag');

        $fieldValue = $model->getZendDate();


        $session = new Zend_Session_Namespace();

        $format_de = "dd.MM.YYYY";
        $format_en = "YYYY/MM/dd";

        switch($session->language) {
            case 'de' :
                $format = $format_de;
                break;
            default:
                $format = $format_en;
                break;
        }

        $timestamp = $model->getUnixTimestamp();
        if (empty($timestamp)) {
            $widget->setValue(null);
        }
        else {
            $widget->setValue($fieldValue->get($format));
        }
        
        $widget->setLabel($fieldName);

        $widget->setRequired(false);

//        $this->__addDescription($modelName . '_' . $fieldName, $widget);
        $widget->addValidators($this->__getDateValidator());
        $widget->setAttrib('class', $fieldName);
        $fieldForm->addElement($widget);
        $fieldForm->removeDecorator('Fieldset');

        return $fieldForm;
    }

    /**
     * Returns a subform for a field.
     *
     * @param  Opus_Model_Field  $field The field to render a subform for.
     * @return Zend_Form_SubForm The subform for the field.
     */
    private function __buildFieldForm(Opus_Model_Field $field, $modelName = '') {
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
            $this->__addDescription( $modelName . '_' . $fieldName . '_field', $helpElement);
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
                        $fieldForm->addSubForm($this->__buildModelForm($fieldValue), $i + 1);
                        $this->__addDescription($modelName . '_' . $fieldName, $fieldForm);
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
                    $this->__addDescription($modelName . '_' . $fieldName, $element);
                    $fieldForm->addElement($element);
                }
            }
        }

        $this->_handleMultiplicity($fieldForm, $field, $fieldValues, $modelName);

        $fieldForm->setAttrib('class', $valueModelClass);
        return $fieldForm;
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

        $this->__addDescription($modelName . '_' . $fieldName, $widget);
        if (false === is_null($validator)) {
            $widget->addValidator($validator);
        }
        $widget->setAttrib('class', $fieldName);
        $fieldForm->addElement($widget);
        $fieldForm->removeDecorator('Fieldset');
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
        $this->__addDescription($modelName . '_' . $fieldName, $widget);
        $fieldForm->addElement($widget);
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
        $fieldName = $field->getName();
        // Create button to add value when appropriate.
        if (count($fieldValues) < $field->getMultiplicity() or '*' === $field->getMultiplicity()) {
            // add an empty element to allow help- and hint texts for whole subfields
            $helpElement = new Zend_Form_Element_Hidden('nothing');
            $helpElement->setAttrib('class', 'hiddenelement');
            $this->__addDescription($modelName . '_' . $fieldName . '_field', $helpElement);
            $fieldForm->addElement($helpElement);

            $element = new Zend_Form_Element_Submit('add');
            $element->removeDecorator('DtDdWrapper');
            $element->setBelongsTo('Actions');
            $element->setLabel('add_' . $fieldName);
            $element->setAttrib('name', 'Action');
            $this->__addDescription($modelName . '_' . $fieldName . '_add', $element);
            $element->setAttrib('class', 'description');
            $element->setAttrib('class', 'button add');
            $fieldForm->addElement($element);
        }
    }

    protected function _createFileField($fieldName, $modelName) {
        // Hardcoded class name for file inputs!
        $fileInput = new Zend_Form_Element_File($fieldName);
        $fileInput->setLabel($fieldName);
        $this->__addDescription($modelName . '_' . $fieldName, $fileInput);
        return $fileInput;
    }

    /**
     * FIXME remove
     * @param <type> $key
     * @param <type> $element
     */
    private function __addDescription($key, $element) {
        $helper = new Form_Builder_Helper_Abstract();
        $helper->_addDescription($key, $element);
    }

    private function __getDateValidator() {
        $format_de = "dd.MM.YYYY";
        $format_en = "YYYY/MM/dd";

        $session = new Zend_Session_Namespace();

        $lang = $session->language;
        $validators = array();

        switch ($lang) {
            case 'en' : $validator = new Zend_Validate_Date(array('format' => $format_en, 'locale' => $lang));
                break;
            case 'de' : $validator = new Zend_Validate_Date(array('format' => $format_de, 'locale' => $lang));
                break;
            default : $validator = new Zend_Validate_Date(array('format' => $format_en, 'locale' => $lang));
                break;
        }
        $messages = array(
            Zend_Validate_Date::INVALID => 'validation_error_date_invalid',
            Zend_Validate_Date::INVALID_DATE => 'validation_error_date_invaliddate',
            Zend_Validate_Date::FALSEFORMAT => 'validation_error_date_falseformat');
        $validator->setMessages($messages);

        $validators[] = $validator;

        return $validators;
    }

}
