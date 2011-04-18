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
 * @package     Module_Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */


class Admin_Model_DocumentHelper {

    private $__document;


    /**
     *
     * @var <type>
     *
     * TODO reverse configuration: fieldName -> group?
     * TODO how about sorting?
     */
    private $fieldGroups = array(
        'dates' => array(
            'PublishedDate',
            'PublishedYear',
            'CompletedDate',
            'CompletedYear',
            'ThesisDateAccepted',
            'ServerDatePublished',
            'ServerDateModified'
        ),
        'general' => array(
            'Language',
            'ServerState',
            'Type'
        ),
        'thesis' => array(
            'ThesisGrantor',
            'ThesisPublisher',
            'ThesisDateAccepted'
        ),
        'titles' => array(
            'TitleMain',
            'TitleParent',
            'TitleSub',
            'TitleAdditional'
        ),
        'abstracts' => array(
            'TitleAbstract'
        ),
        'persons' => array(
            'PersonAuthor',
            'PersonSubmitter',
            'PersonAdvisor',
            'PersonContributor',
            'PersonEditor',
            'PersonReferee',
            'PersonTranslator',
            'PersonOther'
        ),
        'subjects' => array(
            'Subject',
            'SubjectSwd'
        ),
        'other' => array(
            'ContributingCorporation',
            'CreatingCorporation',
            'Edition',
            'Issue',
            'PageFirst',
            'PageLast',
            'PageNumber',
            'PublisherName',
            'PublisherPlace',
            'PublicationState',
            'Volume',
            'BelongsToBibliography'
        ),
        'enrichments' => array(
            'Enrichment'
        ),
        'files' => array(
            'File'
        ),
        'notes' => array(
            'Note'
        ),
        'patents' => array(
            'Patent'
        )
    );

    // TODO actual values are different
    private $identifierTypes = array(
        'old',
        'serial',
        'uuid',
        'isbn',
        'urn',
        'doi',
        'handle',
        'url',
        'issn',
        'std-doi',
        'cris-link',
        'splash-url',
        'opus3-id',
        'opac-id',
        'arxiv',
        'pmid'
    );

    private $referenceTypes = array(
        'isbn',
        'urn',
        'doi',
        'handle',
        'url',
        'issn',
        'std-doi',
        'cris-link',
        'splash-url',
        'opus4-id'
    );

    private $personRoles = array(
        'Advisor',
        'Author',
        'Contributor',
        'Editor',
        'Referee',
        'Other',
        'Translator',
        'Submitter'
    );

    private $personFields = array(
        'AcademicTitle',
        'FirstName',
        'LastName',
        'DateOfBirth',
        'PlaceOfBirth',
        'Email'
    );

    public function __construct($document) {
        $this->__document = $document;
    }

    /**
     * Returns fields for a defined group.
     *
     * @param <type> $groupName
     * @return <type>
     *
     * TODO filter empty values?
     */
    public function getFieldsForGroup($groupName, $filterEmpty = true) {
        $groupFields = array();

        $groupFieldNames = $this->fieldGroups[$groupName];
        
        foreach ($groupFieldNames as $name) {
            $field = $this->__document->getField($name);
            $value = $field->getValue();
            if (!empty($value) || !$filterEmpty) {
              $groupFields[] = $field;
            }
        }

        return $groupFields;
    }

    protected function groupFields() {
    }

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

    public function getValues($model) {
        $values = $model->toArray();
        $result = array();

        foreach ($values as $key => $value) {
            if (!empty($value)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     *
     * @param <type> $date
     * @return <type>
     *
     * TODO create central util class
     */
    public function formatDate($date) {
        if (!($date instanceof Opus_Date)) {
            return $date;
        }

        $session = new Zend_Session_Namespace();

        $format_de = "dd.MM.YYYY";
        $format_en = "YYYY/MM/dd";

        switch($session->language) {
           case 'de':
               $format = $format_de;
               break;
           default:
               $format = $format_en;
               break;
        }

        $timestamp = $date->getUnixTimestamp();

        if (empty($timestamp)) {
            return null;
        }
        else {
            return $date->getZendDate()->get($format);
        }
    }

    /**
     *
     * @param <type> $field
     * @return <type>
     *
     * TODO replicates part of ShowModel helper (todo separate value formatting from layout)
     */
    public function formatField($field) {
        $modelClass = $field->getValueModelClass();

        if (!empty($modelClass)) {
            switch ($modelClass) {
                case 'Opus_Date':
                    return $this->formatDate($field->getValue());
                case 'Opus_Note':
                    return 'TODO handle Opus_Note';
                case 'Opus_Patent':
                    return 'TODO handle Opus_Patent';
                default:
                    // TODO handle other models
                    break;
            }
        }
        else {
            return $field->getValue();
        }
    }

    /**
     *
     * @param Opus_Model_Abstract $value
     * @return Opus_Model_Abstract
     *
     * TODO some values need to be translated (others don't)
     * TODO problem is that: can't iterator over fields
     * TODO can't get list of allowed values from model
     * TODO some things have special methods (Person->getDisplayName())
     */
    public function formatValue($value) {
        if ($value instanceof Opus_Model_Abstract) {
            return $this->formatField($value);
        }
        else {
            return $value;
        }
    }

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
     * Returns possible types for Opus_Identifier.
     * @return array
     */
    public function getIdentifierTypes() {
        return $this->identifierTypes;
    }

    /**
     * Returns possible roles for Opus_Person.
     * @return array
     */
    public function getPersonRoles() {
        return $this->personRoles;
    }

    /**
     * Returns possible types for Opus_Reference.
     * @return array
     */
    public function getReferenceTypes() {
        return $this->referenceTypes;
    }

    public function getForm($model, $includedFields) {
        $fields = $model->toArray();
        foreach ($fields as $key => $value) {
            $field = $model->getField($key);
            if (!empty($field)) {
                $valueModelClass = $field->getValueModelClass();
                switch ($valueModelClass) {
                    case 'Opus_Date':
                        break;
                    default:
                        break;
                }
            }
        }
    }

}


?>
