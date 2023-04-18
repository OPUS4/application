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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Date;
use Opus\Common\DnbInstitute;
use Opus\Common\LoggingTrait;
use Opus\Common\Model\FieldInterface;
use Opus\Common\Model\ModelInterface;

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
    use LoggingTrait;

    /**
     * Controller helper for translations.
     *
     * @var Application_Controller_Action_Helper_Translation
     */
    private $translation;

    /**
     * Controller helper for handling of dates.
     *
     * @var Application_Controller_Action_Helper_Dates
     */
    private $dates;

    /**
     * Constructs Application_View_Helper_FormatValue.
     */
    public function __construct()
    {
        $this->translation = Zend_Controller_Action_HelperBroker::getStaticHelper('Translation');
        $this->dates       = Zend_Controller_Action_HelperBroker::getStaticHelper('Dates');
    }

    /**
     * Returns instance of the view helper.
     *
     * @return $this
     */
    public function formatValue()
    {
        return $this;
    }

    /**
     * Formats value that is instance of AbstractModel.
     *
     * @param FieldInterface $field
     * @param string|null    $model
     * @return string Formatted output
     */
    public function formatModel($field, $model = null)
    {
        if ($field instanceof Date) {
            return $this->formatDate($field);
        } else {
            $modelClass = $field->getValueModelClass();

            $this->getLogger()->debug('Formatting field ' . $field->getName());

            if (! empty($modelClass)) {
                switch ($modelClass) {
                    case Date::class:
                        return $this->formatDate($field->getValue());
                    case DnbInstitute::class:
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
                    $key = $this->translation->getKeyForValue($model, $field->getName(), $value);
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
     * Returns Date values formatted as string.
     *
     * @param Date $date
     * @return string Formatted date
     */
    public function formatDate($date)
    {
        if (! $date instanceof Date) {
            return $date;
        } else {
            return $this->dates->getDateString($date);
        }
    }

    /**
     * Formats value for output on metadata overview page.
     *
     * @param mixed       $value
     * @param string|null $model
     * @return string Formatted output
     *
     * TODO some values need to be translated (others don't)
     * TODO problem is that: can't iterator over fields
     * TODO can't get list of allowed values from model
     * TODO some things have special methods (Person->getDisplayName())
     */
    public function format($value, $model = null)
    {
        if ($value instanceof ModelInterface) {
            return $this->formatModel($value, $model);
        }
        if ($value instanceof FieldInterface) {
            return $this->formatModel($value, $model);
        } else {
            $this->getLogger()->debug('Formatting ' . $value);
            return $value;
        }
    }
}
