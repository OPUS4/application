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
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Unterformular fuer die mit einem Dokument verknuepften Personen.
 */
class Admin_Form_DocumentPersons extends Admin_Form_AbstractDocumentSubForm {
    
    /**
     * Bestimmt die Reihenfolge der Sektionen für die einzelnen Rollen.
     * @var array
     */
    private static $personRoles =  array(
        'author', 'editor', 'translator', 'contributor', 'other', 'advisor', 'referee', 'submitter'
    );

    /**
     * Erzeugt Unterformular für Personen.
     * 
     * Für jede mögliche Rolle wird ein Unterformular angelegt.
     */
    public function init() {
        parent::init();

        $this->setLegend('admin_document_section_persons');
        
        $this->addElement('submit', 'Sort', array('label' => 'Sort'));
        
        foreach (self::$personRoles as $roleName) {
            $subform = new Admin_Form_DocumentPersonRole($roleName);
            $this->addSubForm($subform, $roleName);
        }        
        
        // TODO add button für alle Rollen?
    }
    
    /**
     * 
     * @param Opus_Document $model
     */
    public function populateFromModel($document) {
        $subforms = $this->getSubForms();
        
        foreach ($subforms as $subform) {
            $subform->populateFromModel($document);
        }
    }
    
    /**
     * Konstruiert Formular basierend auf POST Informationen.
     * 
     * Die Teilbereiche des POST werden an die entsprechenden Unterformulare weitergereicht.
     * 
     * @param array $post
     */
    public function constructFromPost($post, $document = null) {
        foreach($post as $key => $data) {
            $subform = $this->getSubForm($key);
            if (!is_null($subform)) {
                $subform->constructFromPost($data, $document);
            }
        }
    }

    /**
     * Verarbeitet einen POST um die notwendigen Aktionen zu ermitteln.
     * @param array $data POST Daten fur dieses Formular
     * @param array $context Komplette POST Daten
     */
    public function processPost($post, $context) {
        foreach ($post as $index => $data) {
            $subform = $this->getSubForm($index);
            if (!is_null($subform)) {
                $result = $subform->processPost($data, $context);

                if (!is_null($result)) {
                    $action = (is_array($result)) ? $result['result'] : $result;

                    switch ($action) {
                        case Admin_Form_DocumentPersonRoles::RESULT_CHANGE_ROLE:
                            $role = $result['role'];
                            $subFormName = $result['subformName'];
                            $personForm = $subform->getSubForm($subFormName);
                            $subform->removeSubForm($subFormName);
                            $this->getSubForm($role)->addSubFormForPerson($personForm); // TODO Seiteneffekte?
                            return Admin_Form_Document::RESULT_SHOW;
                            break;
                        default:
                            return $result;
                            break;
                    }
                }
            }
        }
        
        return null;
    }

    /**
     * 
     * @param type $document
     */
    public function updateModel($document) {
        $subforms = $this->getSubForms();
        
        $persons = array();
        
        foreach ($subforms as $subform) {
            $personsInRole = $subform->getPersons($document);
            $persons = array_merge($persons, $personsInRole);
        }
        
        $document->setPerson($persons);
    }
    
    public function continueEdit($request) {        
        $subforms = $this->getSubForms();
        
        foreach ($subforms as $name => $subform) {
            $subform->continueEdit($request);
        }
    }
    
    public static function getRoles() {
        return self::$personRoles;
    }

}
