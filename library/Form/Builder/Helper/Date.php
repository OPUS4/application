<?php
/*
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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Form_Builder_Helper_Date extends Form_Builder_Helper_Default {

    protected function processFields($model, $subForm) {
        $fieldForm = $this->buildForm($model);
        $subForm->addSubForm($fieldForm, 'date');
    }

    public function buildForm($model) {
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

    public function populateModel(Opus_Model_Abstract $model, array $data) {
        $this->log->debug('populateModel: Opus_Date');
        $dateStr = $data['date'][1];
        $date = new Zend_Date($dateStr);
        $model->setZendDate($date);
        return;
    }

}

?>
