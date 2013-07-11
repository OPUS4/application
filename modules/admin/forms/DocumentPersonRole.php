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
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Unterformular für die Personen in einer bestimmten Role für ein Dokument.
 */
class Admin_Form_DocumentPersonRole extends Admin_Form_DocumentMultiSubForm {
    
    /**
     * Name fuer Button um Person hinzuzufuegen.
     */
    const ELEMENT_ADD = 'Add';
            
    /**
     * Name der Rolle fuer Personen im Unterformular.
     * @var type 
     */
    private $__roleName;

    /**
     * Konstruiert Unterformular fuer Personen in einer Rolle.
     * @param string $roleName
     * @param mixed $options
     */
    public function __construct($roleName, $options = null) {
        $this->__roleName = $roleName;

        // __construct ruft init Funktion auf
        parent::__construct('Admin_Form_DocumentPerson', 'Person' . ucfirst($roleName), null, $options);
    }
    
    /**
     * Liefert Namen der Rolle fuer dieses Unterformular.
     * @return string
     */
    public function getRoleName() {
        return $this->__roleName;
    }

    /**
     * Verarbeitet die POST Daten für dieses Formular.
     * @param array $post
     * @param array $context
     * @return string
     */
    public function processPost($post, $context) {
        $result = parent::processPost($post, $context);
        
        if (!is_null($result)) {
            $action = (is_array($result)) ? $result['result'] : $result;

            switch ($action) {
                case Admin_Form_DocumentPersonMoves::RESULT_MOVE:
                    $move = $result['move'];
                    $subFormName = $result['subformName'];
                    $this->moveSubForm($subFormName, $move);
                    $result = Admin_Form_Document::RESULT_SHOW;
                    break;
                case Admin_Form_Document::RESULT_SWITCH_TO:
                    // Ergebnis (Edit) mit Rolle anreichern
                    $result['target']['role'] = $this->__roleName;
                    break;
                default:
                    // do nothing
                    break;
            }
        }
        
        $this->sortSubFormsBySortOrder();
        
        return $result;
    }
    
    /**
     * Manipuliert das Unterformular, so daß es bei der Sortierung an der gewünschten Position landet.
     * 
     * Nach der POST Verarbeitung werden die Unterformulare sortiert. Wenn eine Person verschoben werden soll, wird
     * vor der Sortierung der SortOrder Wert auf die Zielposition gesetzt, bzw. bei "Down" auf eine Position weiter, 
     * und die ursprüngliche Position (Order) wird auf -1 gesetzt. 
     * @param string $subFormName
     * @param string $direction
     */
    public function moveSubForm($subFormName, $direction) {
        $subform = $this->getSubForm($subFormName);
        
        switch ($direction) {
            case 'First':
                $position = 0;
                break;
            case 'Up':
                $position = $subform->getOrder() - 1;
                if ($position < 0) {
                    $position = 0;
                }
                break;
            case 'Down':
                $position = $subform->getOrder() + 2;
                $subFormCount = $this->getSubForms();
                if ($position > count($subFormCount)) {
                    $position = $subFormCount();
                }
                break;
            case 'Last':
                $position = count($this->getSubForms());
                break;
        }
        
        $subform->setOrder(-1);
        $subform->getElement(Admin_Form_DocumentPerson::ELEMENT_SORT_ORDER)->setValue($position + 1);
    }
        
    public function insertSubForm($newSubForm, $position) {
        $subFormCount = count($this->getSubForms());
        
        if ($position > $subFormCount) {
            $position = $subFormCount;
        }
        else if ($position < 0) {
            $position = 0;
        }
        
        $subforms = $this->getSubForms();
        
        $renamedSubForms = array();
        
        $pos = 0;

        foreach ($subforms as $index => $subform) {
            if ($pos == $position) {
                $pos++;
            }
            $subform->setOrder($pos);
            $name = $this->getSubFormBaseName() . $pos;
            $renamedSubForms[$name] = $subform;
            $pos++;
        }
        
        $newSubForm->setOrder($position);
        $name = $this->getSubFormBaseName() . $position;
        $renamedSubForms[$name] = $newSubForm;
        
        // Formulare Sortieren 
        uksort($renamedSubForms, function($value1, $value2) {
            return $value1 < $value2 ? -1 : 1;
        });
        
        $this->setSubForms($renamedSubForms);
    }    
    
    /**
     * Sortiert die Personen Unterformulare anhand der SortOrder Werte.
     * 
     * Es muss ein Unterschied gemacht werden zwischen einem modifizierten SortOrder Wert und einem Wert der gleich der
     * Order des Unterformulars ist ($form->getOrder() == SortOrder-Value). Wenn ich bei einer Person, z.B. der 4-ten,
     * das SortOrder Feld auf 2 setze, heißt das, daß diese Person auf die Position 2 wechseln soll und alle Personen
     * ab dort einen Schritt nach unten rutschen.
     */
    public function sortSubFormsBySortOrder() {
        $subforms = $this->getSubForms();
        
        $digitsOrder = strlen(count($subforms));
        $digitsSortOrder = strlen($this->getMaxSortOrder($subforms));
        
        $sorted = array();
        
        foreach ($subforms as $name => $subform) {
            $sortKey = $this->getSortKey($subform, $digitsSortOrder, $digitsOrder);
            $sorted[$subform->getName()] = $sortKey; 
        }
        
        asort($sorted);

        $pos = 0;
        
        $subforms = array();
        
        foreach ($sorted as $name => $order) {
            $subform = $this->getSubForm($name);
            $subform->setOrder($pos);
            $subforms[$this->getSubFormBaseName() . $pos] = $subform;
            $pos++;
        }
        
        $this->setSubForms($subforms);
    }
    
    public function getMaxSortOrder($subforms) {
        $maxSortOrder = 0;
        foreach ($subforms as $subform) {
            $sortOrder = $subform->getElement(Admin_Form_DocumentPerson::ELEMENT_SORT_ORDER)->getValue();
            if ($sortOrder > $maxSortOrder) {
                $maxSortOrder = $sortOrder;
            }
        }
        return $maxSortOrder;
    }

    /**
     * Konstruiert einen Schlüssel für die Sortierung der Personen Formulare.
     * 
     * Der Schlüssel hat folgende Struktur. 
     * 
     * SORTORDER_MODIFIED_OLDPOSITION
     * 
     * Zuerst kommt die gewünscht SortOrder, dann kommt ein Flag, ob die SortOrder modifiziert wurde, also nicht mehr
     * der aktuellen Position entspricht, und zum Schluss kommt die alte Position. 
     * 
     * Um beliebig viele Unterfomulare oder beliebig große SortOrder Werte zu unterstützen werden diese mit einer festen
     * Länge, gegebenfalls mit 0 aufgefüllt ausgegeben. Die Anzahl der Digits wird übergeben, damit die Berechnung nicht
     * für jeden Schlüssel erfolgen muss.
     *
     * @param type $subform
     * @return type
     */
    public function getSortKey($subform, $digitsSortOrder = 2, $digitsOrder = 2) {
        $sortOrder = $subform->getElement(Admin_Form_DocumentPerson::ELEMENT_SORT_ORDER)->getValue();
        $order = $subform->getOrder() + 1;
        $modified = ($sortOrder == $order) ? 1 : 0; // NICHT MODIFIZIERT (1) : MODIFIZIERT (0)
        return sprintf('%1$0' . $digitsSortOrder. 'd_%2$d_%3$0' . $digitsOrder. 'd', $sortOrder, $modified, $order);
    }
    
    /**
     * Verarbeitet Klick auf Add Button für Personen.
     * 
     * Die Funktion von Admin_Form_DocumentMultiSubForm wurde überschrieben, damit ein anderes Ergebnis an den 
     * Controller weitergegeben werden kann und ein Wechsel auf eine andere Seite erfolgt, um eine Person hinzufügen 
     * zu können.
     * 
     * @return array
     */
    protected function processPostAdd() {
        // Hinzufuegen wurde ausgewaehlt
        return array( 'result' => Admin_Form_Document::RESULT_SWITCH_TO, 
            'target' => array(
            'module' => 'admin',
            'controller' => 'person',
            'action' => 'assign',
            'role' => $this->__roleName)
        );
    }
    
    /**
     * 
     * @param type $document
     * 
     * TODO Personen sortiert zurück liefern
     * TODO Personen mit geänderter Rolle berücksichtigen
     */
    public function getSubFormModels($document = null) {
        $subforms = $this->getSubForms();
        
        $persons = array();
        
        foreach($subforms as $name => $subform) {
            $person = $subform->getLinkModel($document->getId(), $this->__roleName); // TODO should return Link Objekt
            $persons[] = $person;
        }
        
        return $persons;
    }
    
    /**
     * Fügt ein Person-SubForm hinzu, daß vorher eine andere Rolle hatte.
     * 
     * @param type $subForm
     */
    public function addSubFormForPerson($subForm) {
        // Unterformular vorbereiten
        $rolesForm = new Admin_Form_DocumentPersonRoles($this->__roleName);
        $subForm->addSubForm($rolesForm, 'Roles');

        // Unterformular einfügen
        $position = count($this->getSubForms());
        $subForm->setOrder($position);
        $this->_setOddEven($subForm);
        $this->addSubForm($subForm, $this->getSubFormBaseName() . $position);
    }
                         
    protected function prepareSubFormDecorators($subform) {
        // do nothing
    }
    
    /**
     * Überschrieben, damit die Unterformular Elemente nicht gruppiert werden.
     * @param type $subform
     */
    protected function addRemoveButton($subform) {
        $subform->addElement($this->createRemoveButton());
    }
        
    /**
     * Erzeugt neues Unterformular für eine Person.
     * @return \Admin_Form_DocumentPerson
     */
    public function createNewSubFormInstance() {
        $subform = new Admin_Form_DocumentPerson();
        
        $rolesForm = new Admin_Form_DocumentPersonRoles($this->__roleName);
        $subform->addSubForm($rolesForm, 'Roles');
        
        $movesForm = new Admin_Form_DocumentPersonMoves();
        $subform->addSubForm($movesForm, 'Moves');
                        
        return $subform;
    }
        
    /**
     * Wird nach dem Rücksprung von Add/Edit Seite für Person aufgerufen, um das Ergebnis ins Formular einzubringen.
     * 
     * @param type $request
     */
    public function continueEdit($request) {
        $role = $request->getParam('role', null);
        
        if (!is_null($role) && $role == $this->__roleName) {
            $personId = $request->getParam('person', null);
            
            $action = $request->getParam('continue', null);

            if (!is_null($personId) && $action !== 'updateperson') {
                $person = new Opus_Person($personId);

                $subform = $this->addPersonSubForm(count($this->getSubForms()), $person);
                $subform->getElement(Admin_Form_DocumentPerson::ELEMENT_ROLE)->setValue($role);
                
                $order = $request->getParam('order', null);
                $subform->getElement(Admin_Form_DocumentPerson::ELEMENT_SORT_ORDER)->setValue($order);
                
                $allow = $request->getParam('contact', null);
                $subform->getElement(Admin_Form_DocumentPerson::ELEMENT_ALLOW_CONTACT)->setValue($allow);
                
                $subform->getElement(Admin_Form_Person::ELEMENT_PERSON_ID)->setValue($personId);
            }
            else {
                // TODO deal with it
            }
        }
    }
    
}
