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
 * @package     Module_Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Helper functions for the overview page für documents.
 *
 * The terms 'group' and 'section' are used synonymous. The fields are grouped
 * in sections of the overview page for a document.
 */
class Admin_Model_DocumentHelper {

    /**
     * Model classes for sections of metadata form.
     * @var hash section name => model class
     */
    private static $sectionModel = array(
        'titles' => 'Opus_Title',
        'abstracts' => 'Opus_TitleAbstract',
        'identifiers' => 'Opus_Identifier',
        'references' => 'Opus_Reference',
        'subjects' => 'Opus_Subject',
        'patents' => 'Opus_Patent',
        'notes' => 'Opus_Note',
        'enrichments' => 'Opus_Enrichment'
    );

    /**
     * Field name for sections of metadata form.
     * @var hash section name => field name
     */
    private static $sectionField = array(
        'persons' => 'Person',
        'licences' => 'Licence',
        'series' => 'Series'

    );

    /**
     * Document that is viewed.
     * @var Opus_Document
     */
    private $__document;

    /**
     * Constructs Admin_Model_DocumentHelper for document.
     */
    public function __construct($document) {
        $this->__document = $document;
    }

    /**
     * Returns true if at least on field in a section has a value.
     * @param string Name of section
     * @return boolean True if at least one field in the section has a value
     */
    public function hasValues($groupName) {
        $fields = $this->getFieldsForGroup($groupName);

        foreach($fields as $field) {
            $value = $field->getValue();
            if (!empty($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns fields for a section of the overview form.
     *
     * By default only the fields are returned that contain values. The
     * filtering can be turned off using 'false' as second parameter.
     *
     * @param string Name of section in metadata form
     * @param boolean Filtering enabled (default true)
     * @return array Opus_Model_Field objects for document
     */
    public function getFieldsForGroup($groupName, $filterEmpty = true) {
        $groupFields = array();

        $groupFieldNames = $this->getFieldNamesForGroup($groupName);

        foreach ($groupFieldNames as $name) {
            $field = $this->__document->getField($name);
            $value = $field->getValue();
            if (!empty($value) || !$filterEmpty) {
              $groupFields[] = $field;
            }
        }

        return $groupFields;
    }

    /**
     * Returns the collections grouped by CollectionRole.
     * @return array Collections grouped by CollectionRole
     */
    public function getGroupedCollections() {
        $groupedCollections = array();

        foreach($this->__document->getCollection() as $collection) {

            $roleName = $collection->getRoleName();

            if (!isset($groupedCollections[$roleName])) {
                $groupedCollections[$roleName] = array();
            }

            $collections = $groupedCollections[$roleName];

            $collections[] = $collection;

            $groupedCollections[$roleName] = $collections;
        }

        return $groupedCollections;
    }

    /**
     * Returns subjects grouped by type.
     * @return array Subjects grouped by type
     */
    public function getGroupedSubjects() {
        $groupedSubjects = array();

        foreach($this->__document->getSubject() as $subject) {

            $subjectType = $subject->getType();

            if (!isset($groupedSubjects[$subjectType])) {
                $subjects = array();
            }
            else {
                $subjects = $groupedSubjects[$subjectType];
            }

            $subjects[] = $subject;

            $groupedSubjects[$subjectType] = $subjects;
        }

        return $groupedSubjects;
    }

    /**
     * Returns the field values of a model as array.
     * @param Opus_Model_Abstract Model (e.g. Opus_Person, ...)
     * @return array Field values
     */
    public function getValues($model) {
        $values = $model->toArray();
        $result = array();

        // Iterate through array representation of Model
        foreach ($values as $key => $value) {
            // Only include fields that are not empty
            if (!empty($value)) {
                // TODO review (hack)
                // Check if field is a Date field (by name)
                if (strpos($key, 'Date') !== FALSE) {
                    // Get value of Date field and add to result
                    $field = $model->getField($key);
                    $result[$key] = $field->getValue();
                }
                else {
                    // Add key and value to result array
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Returns field instances for a model instance.
     *
     * Empty fields are not included.
     *
     * @param Opus_Model_Abstract $model
     * @return array Opus_Model_Field instances
     */
    public function getFields($model) {
        $fieldNames = $model->describe();

        $fields = array();

        foreach ($fieldNames as $name) {
            $field = $model->getField($name);
            $value = $field->getValue();
            if (!empty($value)) {
                $fields[$name] = $field;
            }
        }

        return $fields;
    }

    /**
     * Returns array of values from multiple fields.
     *
     * For instance a single array containing different Opus_Title fields.
     * Use for 'titles', 'persons', and 'patents' section.
     *
     * @param Opus_Model_Field Fields that need to be combined
     * @return array Values from different fields
     */
    public function flattenValues($fields) {
        $result = array();

        foreach ($fields as $index => $field) {
            $values = $field->getValue();
            foreach($values as $index2 => $value) {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * Returns the field names for a section in the metadata form.
     * @param string $section Name of metadata form section
     * @return array Field names for section
     */
    public static function getFieldNamesForGroup($section) {
        $config = Admin_Model_DocumentHelper::getMetadataConfig();

        $data = $config->toArray();

        if (array_key_exists($section, $data)) {
            return $data[$section];
        }
        else {
            return null;
        }
    }

    /**
     * Returns the names of the groups (sections) for the metadata form.
     * @return array of group names
     */
    public static function getGroups() {
        $config = Admin_Model_DocumentHelper::getMetadataConfig();

        $data = $config->toArray();

        return array_keys($data);
    }

    /**
     * Returns the field name for a group (section).
     * @param string $group Name of group (section)
     * @return string Name of field of Opus_Document for group (section)
     */
    public static function getFieldNameForGroup($group) {
        if (isset(self::$sectionField[$group])) {
            return self::$sectionField[$group];
        }
        else {
            return null;
        }
    }

    /**
     * Returns the common model class for a group (section).
     * @param string $group Name of group (section)
     * @param boolean $tryField set to false if valueModelClass for section field should be returned
     * @return string Model class name for group
     */
    public static function getModelClassForGroup($group, $tryField = true) {
        if (isset(self::$sectionModel[$group])) {
            return self::$sectionModel[$group];
        }
        else if ($tryField) {
            $document = new Opus_Document();
            $fieldName = Admin_Model_DocumentHelper::getFieldNameForGroup($group);
            if (!is_null($fieldName)) {
                $field = $document->getField($fieldName);
                if (!is_null($field)) {
                    return $field->getValueModelClass();
                }
            }
        }

        return null;
    }

    /**
     * Checks if a group name is valid.
     * @return boolean True - if the group name is valid.
     */
    public static function isValidGroup($group) {
        $groups = Admin_Model_DocumentHelper::getGroups();

        return in_array($group, $groups);
    }

    /**
     * Configuration of sections and fields for metadata form.
     * @var Zend_Config_Ini
     */
    private static $__metadataConfig;

    /**
     * Returns configuration of sections and fields.
     *
     * The function loads the configuration, if not already loaded.
     *
     * @return Zend_Config_Ini containing section and fields configuration
     */
    private static function getMetadataConfig() {
        if (empty(Admin_Model_DocumentHelper::$__metadataConfig)) {
            Admin_Model_DocumentHelper::$__metadataConfig = new Zend_Config_Ini(
                    APPLICATION_PATH . '/modules/admin/models/sections.ini');
        }

        return Admin_Model_DocumentHelper::$__metadataConfig;
    }

}
