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
 * @category   Application
 * @package    View
 * @author     Henning Gerhardt (henning.gerhardt@slub-dresden.de)
 * @copyright  Copyright (c) 2009, OPUS 4 development team
 * @license    http://www.gnu.org/licenses/gpl.html General Public License
 * @version    $Id$
 */

/**
 * View helper for displaying a model
 *
 * @category    Application
 * @package     View
 */
class View_Helper_ShowModel extends Zend_View_Helper_Abstract {

    /**
     * Supress all empty fields
     *
     * @var boolean
     */
    private $__saef = false;

    /**
     * Supress personal informations
     *
     * @var boolean
     */
    private $__spi = false;

    /**
     * Helper method to create a proper array
     *
     * @param string $name   Name of element
     * @param string $value  Value of element
     * @param string $prefix (Optional) Label of element
     * @return array
     */
    private function __skeleton($name, $value, $prefix = null) {
        $result = array();
        $result['divclass'] = $name;
        $result['labelclass'] = $name . ' label';
        $result['valueclass'] = $name . ' value';
        $result['label'] = $name;
        $result['prefix'] = $prefix;
        $result['value'] = $value;
        return $result;
    }

    /**
     * Helper method for complex data
     *
     * @param string $field  Field to display
     * @param array  &$value Value of field
     * @param string $prefix (Optional) Prefix for display field
     * @return string
     */
    private function __complexHelper($field, array &$value, $prefix = null) {
        $result = '';
        $data = array();
        foreach ($value as $fieldname => $internal_value) {
            if (($this->__saef === false) or (empty($internal_value) === false)) {
                $data[] = $this->__skeleton($fieldname, $internal_value);
            }
        }
        if (($this->__saef === false) or (empty($data) === false)) {
            $iterim_data = $this->view->partialLoop('_model.phtml', $data);
            $outer = $this->__skeleton($field, $iterim_data, $prefix);
            $result = $this->view->partial('_model.phtml', $outer);
        }
        return $result;
    }

    /**
     * General method for complex fields
     *
     * @param string $field   Field to display
     * @param mixed  &$values Values of a field
     * @return string
     */
    private function __complexDisplay($field, &$values) {
        $result = '';
        // silence decision about multi values or not
        if (@is_array($values[0]) === false) {
            // only one element to display
            $result = $this->__complexHelper($field, $values);
        } else {
            // more than one element to display
            foreach ($values as $number => $value) {
                $prefix = (++$number) . '. ';
                $result .= $this->__complexHelper($field, $value, $prefix);
            }
        }
        return $result;
    }

    /**
     * Helper method for converting Zend_Date data to a proper string.
     *
     * @param string $field Field to display.
     * @param mixed  $value Value of this field.
     * @return string
     */
    private function __dateHelper($field, $value) {
        if ($value instanceOf Zend_Date) {
            $locale = Zend_Registry::get('Zend_Translate')->getLocale();
            $value->setLocale($locale);
            $values = $value->toString(Zend_Locale_Format::getDateFormat($locale));
        } else {
            $values = $value;
        }
        $result = '';
        if (($this->__saef === false) or (empty($values) === false)) {
                $data[] = $this->__skeleton($field, $values);
        }
        if (($this->__saef === false) or (empty($data) === false)) {
            $result = $this->view->partialLoop('_model.phtml', $data);
        }
        return $result;
    }

    /**
     * Helper method for displaying language field.
     *
     * @param string $field  Contains field name.
     * @param string $value  Contains language information.
     * @param string $prefix (Optional) Prefix for multiple language fields.
     * @return string
     */
    private function __languageHelper($field, $value, $prefix = null) {
        $result = '';
        $language_list = Zend_Registry::get('Available_Languages');
        $iterim_value = @$language_list[$value];
        if (($this->__saef === false) or (empty($iterim_value) === false)) {
            $data = $this->__skeleton($field, $iterim_value, $prefix);
            $result = $this->view->partial('_model.phtml', $data);
        }
        return $result;
    }

    /**
     * Helper method for displaying licence field.
     *
     * @param string $field  Contains field name.
     * @param string &$value Contains licence informations.
     * @param string $prefix (Optional) Prefix for multiple licence fields.
     * @return string
     */
    private function __licenceHelper($field, &$value, $prefix = null) {
        $result = '';
        // we "know" that the licence name is in NameLong
        $display_name = @$value['NameLong'];
        $licence_link = @$value['LinkLicence'];
        if (false === empty($licence_link)) {
            $iterim_value = '<a href="' . $licence_link . '">' . $display_name . '</a>';
        } else {
            $iterim_value = $display_name;
        }
        if (($this->__saef === false) or (empty($iterim_value) === false)) {
            $data = $this->__skeleton($field, $iterim_value, $prefix);
            $result = $this->view->partial('_model.phtml', $data);
        }
        return $result;
    }

    /**
     * Helper method for person data
     *
     * @param string $field  Specific field
     * @param array  &$value Value of field
     * @param string $prefix (Optional) Prefix for field
     * @return string
     */
    private function __personHelper($field, array &$value, $prefix = null) {
        $result = '';
        $data = array();
        // merge academic title, lastname and firstname
        $title = @$value['AcademicTitle'];
        $lastname = @$value['LastName'];
        $firstname = @$value['FirstName'];
        $merged = $title . $lastname;
        if (empty($firstname) === false) {
            $merged .=  ', ' . $firstname;
        }
        $fieldname = 'PersonName';
        if (($this->__saef === false) or (empty($merged) === false)) {
            $data[] = $this->__skeleton($fieldname, $merged);
        }
        if ($this->__spi === false) {
            // other fields
            $other_fields = array('DateOfBirth', 'PlaceOfBirth', 'Email');
            foreach ($other_fields as $fieldname) {
                if (array_key_exists($fieldname, $value) === true) {
                    $fieldValue = $value[$fieldname];
                    if (($this->__saef === false) or (empty($fieldValue) === false)) {
                        if ($fieldValue instanceOf Zend_Date) {
                            $locale = Zend_Registry::get('Zend_Translate')->getLocale();
                            $fieldValue->setLocale($locale);
                            $fieldValue = $fieldValue->toString(Zend_Locale_Format::getDateFormat($locale));
                        }
                        $data[] = $this->__skeleton($fieldname, $fieldValue);
                    }
                }
            }
        }
        if (($this->__saef === false) or (empty($data) === false)) {
            $iterim_data = $this->view->partialLoop('_model.phtml', $data);
            $outer = $this->__skeleton($field, $iterim_data, $prefix);
            $result = $this->view->partial('_model.phtml', $outer);
        }
        return $result;
    }

    /**
     * General method for displaying person data
     *
     * @param string $field   Field to display
     * @param array  &$values Value of field
     * @return string
     */
    private function __personDisplay($field, array &$values) {
        $result = '';
        // silence decision about multi values or not
        if (@is_array($values[0]) === false) {
            // only one element to display
            $result = $this->__personHelper($field, $values);
        } else {
            // more than one element to display
            foreach ($values as $number => $value) {
                $prefix = (++$number) . '. ';
                $result .= $this->__personHelper($field, $value, $prefix);
            }
        }
        return $result;
    }

    /**
     * Helper method for displaying titles or abstracts
     *
     * @param string $field  Field for displaying
     * @param array  &$value Value of field
     * @param string $prefix (Optional) Prefix for displaying field
     * @return string
     */
    private function __titleHelper($field, array &$value, $prefix = null) {
        $data = array();
        // title language
        $language_field = 'Language';
        if (true === array_key_exists($language_field, $value)) {
            $language_list = Zend_Registry::get('Available_Languages');
            $language = $language_list[$value[$language_field]];
            $data[] = $this->__skeleton($language_field, $language);
        }
        // title value
        $title_field = 'Value';
        $iterim_value = $value[$title_field];
        $data[] = $this->__skeleton($field . $title_field, $iterim_value);
        $iterim_data = $this->view->partialLoop('_model.phtml', $data);
        $outer = $this->__skeleton($field, $iterim_data, $prefix);
        return $this->view->partial('_model.phtml', $outer);
    }

    /**
     * General method for displaying titles or abstracts
     *
     * @param string $field   Field to display
     * @param mixed  &$values Value of field
     * @return string
     */
    private function __titleDisplay($field, &$values) {
        $result = '';
        // silence decision about multi values or not
        if (@is_array($values[0]) === false) {
            // only one element to display
            if (($this->__saef === false) or (empty($values) === false)) {
                $result = $this->__titleHelper($field, $values);
            }
        } else {
            // more than one element to display
            foreach ($values as $number => $value) {
                if (($this->__saef === false) or (empty($value) === false)) {
                    $prefix = (++$number) . '. ';
                    $result .= $this->__titleHelper($field, $value, $prefix);
                }
            }
        }
        return $result;
    }

    /**
     * General method for displaying subjects.
     *
     * @param string $field  Contains field name.
     * @param array  &$value Contains subject values.
     * @return string
     */
    private function __subjectHelper($field, array &$value) {
        $result = '';
        $iterim_value = array();
        foreach ($value as $val) {
            $iterim_value[] = $val['Value'];
        }
        $iterim_value = implode(', ', $iterim_value);
        if (($this->__saef === false) or (empty($iterim_value) === false)) {
            $data = $this->__skeleton($field, $iterim_value);
            $result = $this->view->partial('_model.phtml', $data);
        }
        return $result;
    }

    /**
     * General method for displaying collections.
     *
     * @param string $field  Contains field name.
     * @param array  &$value Contains subject values.
     * @return string
     */
    private function __collectionHelper($field, array &$value) {
        $result = '';
        $val = $value['Name'];
        if (($this->__saef === false) or (empty($val) === false)) {
            $data = $this->__skeleton($field, $val);
            $result = $this->view->partial('_model.phtml', $data);
        }
        return $result;
    }

    /**
     * Helper method for displaying urn values.
     *
     * @param string $field  Field to display
     * @param array  &$value Value of field
     * @return string
     */
    private function __urnHelper($field, array &$value) {
        $result = '';
        $urn_value = @$value['Value'];
        if (($this->__saef === false) or (empty($urn_value) === false)) {
            // TODO resolving URI should configurable
            $output_string = 'http://nbn-resolving.de/urn/resolver.pl?' . $urn_value;
            $iterim_value = '<a href="' . $output_string . '">' . $output_string . '</a>';
            $data = $this->__skeleton($field, $iterim_value);
            $result = $this->view->partial('_model.phtml', $data);
        }
        return $result;
    }

    /**
     * General method for displaying a field
     *
     * @param string $name  Field to display
     * @param string $value Value of field
     * @return string
     */
    protected function _displayGeneralElement($name, $value) {
        $result = '';
        if (false === is_array($value)) {
            if (($this->__saef === false) or (empty($value) === false)) {
                $data = $this->__skeleton($name, $value);
                $result = $this->view->partial('_model.phtml', $data);
            }
        } else {
            foreach ($value as $number => $val) {
                if (($this->__saef === false) or (empty($val) === false)) {
                    $data = $this->__skeleton($name, $val);
                    $result .= $this->view->partial('_model.phtml', $data);
                }
            }
        }
        return $result;
    }

    /**
     *  Method for displaying licences.
     *
     * @param string $field Licence field for displaying
     * @param string $value Value of licence field
     * @return string
     */
    protected function _displayLicence($field, $value) {
        $result = '';
        if (false === @is_array($value[0])) {
            if (($this->__saef === false) or (empty($value) === false)) {
                $result = $this->__licenceHelper($field, $value);
            }
        } else {
            foreach ($value as $number => $val) {
                if (($this->__saef === false) or (empty($val) === false)) {
                    $prefix = (++$number) . '. ';
                    $result .= $this->__licenceHelper($field, $val, $prefix);
                }
            }
        }
        return $result;
    }

    /**
     * Method for displaying language field
     *
     * @param string $field Lanugage field to display
     * @param string $value Value of language field
     * @return string
     */
    protected function _displayLanguage($field, $value) {
        $result = '';
        if (false === is_array($value)) {
            if (($this->__saef === false) or (empty($value) === false)) {
                $result = $this->__languageHelper($field, $value);
            }
        } else {
            foreach ($value as $number => $val) {
                if (($this->__saef === false) or (empty($val) === false)) {
                    $prefix = (++$number) . '. ';
                    $result .= $this->__languageHelper($field, $val, $prefix);
                }
            }
        }
        return $result;
    }

    /**
     * Wrapper for note field.
     *
     * @param string $field
     * @param array  &$value
     * @return string
     */
    protected function _displayNote($field, array &$value) {
        return $this->__complexDisplay($field, $value);
    }

    /**
     * Method for displaying files of a document
     *
     * @param string $field Files field for displaying
     * @param string $value Value of files field
     * @return void
     */
    protected function _displayFile($field, $value) {
        // TODO need more information for displaying
        // makes code sniffer happy
        $my_field = $field;
        $my_value = $value;
        return;
    }

    /**
     * Wrapper method for person advisor
     *
     * @param string $field Person field for displaying
     * @param mixed  $value Value of person field
     * @return string
     */
    protected function _displayPersonAdvisor($field, $value) {
        return $this->__personDisplay($field, $value);
    }

    /**
     * Wrapper method for person author
     *
     * @param string $field Person field for displaying
     * @param mixed  $value Value of person field
     * @return string
     */
    protected function _displayPersonAuthor($field, $value) {
        return $this->__personDisplay($field, $value);
    }

    /**
     * Wrapper method for person contributor
     *
     * @param string $field Person field for displaying
     * @param mixed  $value Value of person field
     * @return string
     */
    protected function _displayPersonContributor($field, $value) {
        return $this->__personDisplay($field, $value);
    }

    /**
     * Wrapper method for person editor
     *
     * @param string $field Person field for displaying
     * @param mixed  $value Value of person field
     * @return string
     */
    protected function _displayPersonEditor($field, $value) {
        return $this->__personDisplay($field, $value);
    }

    /**
     * Wrapper method for person referee
     *
     * @param string $field Person field for displaying
     * @param mixed  $value Value of person field
     * @return string
     */
    protected function _displayPersonReferee($field, $value) {
        return $this->__personDisplay($field, $value);
    }

    /**
     * Wrapper method for person other
     *
     * @param string $field Person field for displaying
     * @param mixed  $value Value of person field
     * @return string
     */
    protected function _displayPersonOther($field, $value) {
        return $this->__personDisplay($field, $value);
    }

    /**
     * Wrapper method for person translator
     *
     * @param string $field Person field for displaying
     * @param mixed  $value Value of person field
     * @return string
     */
    protected function _displayPersonTranslator($field, $value) {
        return $this->__personDisplay($field, $value);
    }

    /**
     * Wrapper method for person submitter
     *
     * @param string $field Person field for displaying
     * @param mixed  $value Value of person field
     * @return string
     */
    protected function _displayPersonSubmitter($field, $value) {
        return $this->__personDisplay($field, $value);
    }

    /**
     * Wrapper method for isbn
     *
     * @param string $field Isbn field for displaying
     * @param mixed  $value Value of isbn field
     * @return string
     */
    protected function _displayIdentifierIsbn($field, $value) {
        return $this->__complexDisplay($field, $value);
    }

    /**
     * Wrapper method for title abstract
     *
     * @param string $field Title field for displaying
     * @param mixed  $value Value of title field
     * @return string
     */
    protected function _displayTitleAbstract($field, $value) {
        return $this->__titleDisplay($field, $value);
    }

    /**
     * Wrapper method for title main
     *
     * @param string $field Title field for displaying
     * @param mixed  $value Value of title field
     * @return string
     */
    protected function _displayTitleMain($field, $value) {
        return $this->__titleDisplay($field, $value);
    }

    /**
     * Wrapper method for title parent
     *
     * @param string $field Title field for displaying
     * @param mixed  $value Value of title field
     * @return string
     */
    protected function _displayTitleParent($field, $value) {
        return $this->__titleDisplay($field, $value);
    }

    /**
     * Skipped importer field opus3.
     *
     * @param string $name  Field to display.
     * @param string $value Value of field.
     * @return void
     */
    protected function _displayIdentifierOpus3($field, $value) {
        // make code sniffer happy
        $my_field = $field;
        $my_value = $value;
        return;
    }

    /**
     * An urn field need a special handling for display.
     *
     * @param string $field  Urn field for displaying
     * @param array  &$value Value of urn field
     * @return string
     */
    protected function _displayIdentifierUrn($field, array &$value) {
        $result = '';
        if (false === @is_array($value[0])) {
            $result = $this->__urnHelper($field, $value);
        } else {
            foreach ($value as $number => $val) {
                $result .= $this->__urnHelper($field, $val);
            }
        }
        return $result;
    }

    /**
     * Wrapper method for uncontrolled subjects.
     *
     * @param string $field  Contains field name.
     * @param array  &$value Contains field values.
     * @return string
     */
    protected function _displaySubjectUncontrolled($field, array &$value) {
        return $this->__subjectHelper($field, $value);
    }

    /**
     * Wrapper method for Psyndex subjects.
     *
     * @param string $field  Contains field name.
     * @param array  &$value Contains field values.
     * @return string
     */
    protected function _displaySubjectPsyndex($field, array &$value) {
        return $this->__subjectHelper($field, $value);
    }

    /**
     * Wrapper method for SWD subjects.
     *
     * @param string $field  Contains field name.
     * @param array  &$value Contains field values.
     * @return string
     */
    protected function _displaySubjectSwd($field, array &$value) {
        return $this->__subjectHelper($field, $value);
    }

    /**
     * Wrapper method for displaying ServerDateModified.
     *
     * @param string $field Contains field name.
     * @param array  $value Contains field values.
     * @return string
     */
    protected function _displayServerDateModified($field, $value) {
        return $this->__dateHelper($field, $value);
    }

    /**
     * Wrapper method for displaying ServerDatePublished.
     *
     * @param string $field Contains field name.
     * @param array  $value Contains field values.
     * @return string
     */
    protected function _displayServerDatePublished($field, $value) {
        return $this->__dateHelper($field, $value);
    }

    /**
     * Wrapper method for displaying CompletedDate.
     *
     * @param string $field Contains field name.
     * @param array  $value Contains field values.
     * @return string
     */
    protected function _displayCompletedDate($field, $value) {
        return $this->__dateHelper($field, $value);
    }

    /**
     * Wrapper method for displaying PublishedDate.
     *
     * @param string $field Contains field name.
     * @param array  $value Contains field values.
     * @return string
     */
    protected function _displayPublishedDate($field, $value) {
        return $this->__dateHelper($field, $value);
    }

    /**
     * Wrapper method for displaying PublishedDate.
     *
     * @param string $field Contains field name.
     * @param array  $value Contains field values.
     * @return string
     */
    protected function _displayDateAccepted($field, $value) {
        return $this->__dateHelper($field, $value);
    }

    /**
     * Wrapper method for displaying Publisher.
     *
     * @param string $field Contains field name.
     * @param array  $value Contains field values.
     * @return string
     */
    protected function _displayPublisher($field, $value) {
        return $this->__collectionHelper($field, $value);
    }

    /**
     * Wrapper method for displaying Grantor.
     *
     * @param string $field Contains field name.
     * @param array  $value Contains field values.
     * @return string
     */
    protected function _displayGrantor($field, $value) {
        return $this->__collectionHelper($field, $value);
    }

    /**
     * View helper for displaying a model
     *
     * @param array   &$modeldata Contains model data
     * @param boolean $saef       (Optional) Supress all empty fields.
     * @param boolean $spi        (Optional) Supress personal informations.
     * @return string
     */
    public function showModel(array &$modeldata, $saef = false, $spi = false) {
        if (is_bool($saef) === true) {
            $this->__saef = $saef;
        }
        if (is_bool($spi) === true) {
            $this->__spi = $spi;
        }
        $result = '';
        foreach ($modeldata as $field => $value) {
            if (true === empty($value) || (count($value) === 1 && is_array($value) === true && is_string($value[0]) === true && strlen($value[0]) === 0)) {
                continue;
            }

            $method_name = '_display' . $field;
            if (method_exists($this, $method_name) === true) {
                $result .= $this->$method_name($field, $value);
            } else {
                $result .= $this->_displayGeneralElement($field, $value);
            }
        }
        return $result;
    }
}
