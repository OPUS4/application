<?php

class Admin_Model_DocumentOverviewHelper {

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
          'PublishedYear',
          'CompletedYear',
          'ThesisDateAccepted',
          'ServerDatePublished',
          'ServerDateModified'),
      'general' => array(
          'Language',
          'ServerState',
          'Type'
      )
    );

    public function __construct($document) {
        $this->__document = $document;
    }

    public function getGroupFields($groupName) {
        $groupFields = array();

        $groupFieldNames = $this->fieldGroups[$groupName];
        
        foreach ($groupFieldNames as $name) {
            $field = $this->__document->getField($name);
            $groupFields[] = $field;
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

}


?>
