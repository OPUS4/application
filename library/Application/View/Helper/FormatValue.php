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
 * @package     View
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * View Helper for formatting field values.
 *
 * This view helper is used by the metadata overview page for a document.
 *
 * TODO Explore options to remove overlap with ShowModel view helper
 *      (ShowModel combines value formatting and layout).
 */
class Application_View_Helper_FormatValue extends Zend_View_Helper_Abstract
{

    /**
     * Logger for this class.
     * @var Zend_Log Logger
     */
    private $_logger;

    /**
     * Controller helper for translations.
     * @var Application_Controller_Action_Helper_Translation
     */
    private $_translation;

    /**
     * Controller helper for handling of dates.
     * @var Application_Controller_Action_Helper_Dates
     */
    private $_dates;

    /**
     * Constructs Application_View_Helper_FormatValue.
     */
    public function __construct()
    {
        $this->_translation = Zend_Controller_Action_HelperBroker::getStaticHelper('Translation');
        $this->_dates = Zend_Controller_Action_HelperBroker::getStaticHelper('Dates');
    }

    /**
     * Returns instance of the view helper.
     * @return Application_View_Helper_FormatValue
     */
    public function formatValue()
    {
        return $this;
    }

    /**
     * Formats value that is instance of Opus_Model_Abstract.
     * @param Opus_Model_Abstract $field
     * @param string Name of model for field (default = null)
     * @return string Formatted output
     */
    public function formatModel($field, $model = null)
    {
        if ($field instanceof Opus_Date) {
            return $this->formatDate($field);
        } else {
            $modelClass = $field->getValueModelClass();

            $this->getLogger()->debug('Formatting field ' . $field->getName());

            if (! empty($modelClass)) {
                switch ($modelClass) {
                    case 'Opus_Date':
                        return $this->formatDate($field->getValue());
                    case 'Opus_DnbInstitute':
                        $value = $field->getValue();
                        if (isset($value[0])) {
                            return $value[0]->getName();
                        } else {
                            // Should never happen (DNB Institute without name),
                            // but in case it does:
                            return 'ERROR: DNB institute without name.';
                        }
                        break; // should never be reached
                    default:
                        // Should never happen, but in case it does:
                        $this->getLogger()->err(__CLASS__ . ' Trying to format unknown model ' . $modelClass);
                        return 'ERROR: Unknown model class (see log).';
                }
            } else {
                $value = $field->getValue();

                if ($field->getName() === 'Language') {
                    return $this->view->translateLanguage($value);
                } elseif ($field->isSelection()) {
                    Application_Form_Element_Language::getLanguageList(); // initializes language list translations
                    $key = $this->_translation->getKeyForValue($model, $field->getName(), $value);
                    return $this->view->translate($key);
                } elseif ($field->isCheckbox()) {
                    if ($value) {
                        $key = 'Field_Value_True';
                    } else {
                        $key = 'Field_Value_False';
                    }
                    return $this->view->translate($key);
                } else {
                    return $value;
                }
            }
        }
    }

    /**
     * Returns Opus_Date values formatted as string.
     * @param Opus_Date $date
     * @return string Formatted date
     */
    public function formatDate($date)
    {
        if (! ($date instanceof Opus_Date)) {
            return $date;
        } else {
            return $this->_dates->getDateString($date);
        }
    }

    /**
     * Formats value for output on metadata overview page.
     *
     * @param Field value
     * @param string Name of model for field
     * @return string Formatted output
     *
     * TODO some values need to be translated (others don't)
     * TODO problem is that: can't iterator over fields
     * TODO can't get list of allowed values from model
     * TODO some things have special methods (Person->getDisplayName())
     */
    public function format($value, $model = null)
    {
        if ($value instanceof Opus_Model_Abstract) {
            return $this->formatModel($value, $model);
        }
        if ($value instanceof Opus_Model_Field) {
            return $this->formatModel($value, $model);
        } else {
            $this->getLogger()->debug('Formatting ' . $value);
            return $value;
        }
    }

    /**
     * Returns logger.
     * @return Zend_Log
     */
    private function getLogger()
    {
        if (empty($this->logger)) {
            $this->_logger = Zend_Registry::get('Zend_Log');
        }

        return $this->_logger;
    }
}
