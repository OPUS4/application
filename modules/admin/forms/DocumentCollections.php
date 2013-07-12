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
 * Subform fuer Collections im Metadaten-Formular.
 * 
 * Dieses Formular zeigt die dem Dokument zugewiesenen Collections an. Jede Collection erhält einen "Entfernen" Button
 * um die Zuweisung zu löschen. Außerdem gibt es einen Submit Button der den Nutzer zur Seite für das Zuweisen einer
 * weiteren Collection bringt.
 * 
 * Für jede CollectionRole wird ein Zend_Form_SubForm angelegt. Diesem wiederum wird für jede zugehörige Collection ein
 * Admin_Form_DocumentCollection Unterformular hinzugefügt. Dadurch entsteht eine Hierarchy für die Anzeige und POST
 * Verarbeitung.
 * 
 * <pre>
 * Admin_Form_DocumentCollections
 *   +-Zend_Form_SubForm
 *     +-Admin_Form_DocumentCollection
 * </pre>
 * 
 * Wenn eine neue Collection zugewiesen werden soll, muß dem Controller signalisiert werden, das der aktuelle POST in 
 * der Session gespeichert werden muß und eine neue URL (zum Zuweisen der Collection) angesprungen werden soll. 
 * 
 * TODO eliminiere redundanten Code fuer CollectionRole SubForm (separate Klasse?) (vergl. mit MultiSubForm Klasse)
 */
class Admin_Form_DocumentCollections extends Admin_Form_AbstractDocumentSubForm {
    
    /**
     * Name für Button zum Hinzufügen von Collections.
     */
    const ELEMENT_ADD = 'Add';
    
    /**
     * Initialisiert Elemente für gesamtes Collections Formular.
     */
    public function init() {
        parent::init();
        
        $this->addElement('submit', self::ELEMENT_ADD, array('order' => 1000, 'label' => 'admin_button_add'));
        $this->setLegend('admin_document_section_collection');
    }
    
    /**
     * Erzeugt und initialisiert Unterformulare entsprechend den Collections eines Dokuments.
     * @param Opus_Document $document
     */
    public function populateFromModel($document) {
        $this->clearSubForms();
        
        $docHelper = new Admin_Model_DocumentHelper($document);
        
        $collectionRoles = $docHelper->getGroupedCollections();
        
        // Iteriere über CollectionRole Namen für Dokument und erzeuge Unterformulare
        foreach ($collectionRoles as $roleName => $collections) {
            $roleForm = new Admin_Form_DocumentSection();
            
            $roleForm->setLegend('default_collection_role_' . $roleName);
            
            $position = 0;
            
            // Iteriere über Collections für CollectionRole und erzeuge Unterformulare
            foreach ($collections as $index => $collection) {
                $collectionForm = $this->createCollectionForm($position++);
                $collectionForm->populateFromModel($collection);
                $roleForm->addSubForm($collectionForm, 'collection' . $index);
            }
            
            $this->addSubForm($roleForm, $roleName);
        }
    }
        
    public function processPost($data, $context) {
        if (array_key_exists(self::ELEMENT_ADD, $data)) {
            // Neue Sammlung zuweisen
            return array( 'result' => Admin_Form_Document::RESULT_SWITCH_TO, 
                'target' => array(
                'module' => 'admin',
                'controller' => 'collection',
                'action' => 'assign')
            );
        }
        else {
            // POST Verarbeitung der Unterformular 
            foreach ($data as $roleName => $collections) {
                $roleForm = $this->getSubForm($roleName);

                if (!is_null($roleForm)) {
                    foreach ($collections as $key => $collection) {
                        $colForm = $roleForm->getSubForm($key);

                        $result = $colForm->processPost($collection, $context);

                        if ($result === 'remove') {
                            // TODO move to function _removeSubForm?
                            $roleForm->removeSubForm($colForm->getName());
                            if (count($roleForm->getSubForms()) == 0) {
                                $this->removeSubForm($roleForm->getName());
                            }
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Erzeugt Unterformulare basierend auf den Informationen in den POST Daten.
     */
    public function constructFromPost($post, $document = null) {
        foreach ($post as $roleName => $data) {
            // Prüfen ob Unterformluar (array) oder Feld
            if (is_array($data)) {
                $this->_addSubForm($roleName, $data);
            }
        }
    }
    
    /**
     * Aktualisiert die Liste der zugewiesenen Collections für ein Dokument.
     * 
     * Diese Funktion iteriert über alle Unterformulare und fragt die Collections ab. Die Collections werden in einem
     * Array gesammelt und dann dem Dokument zugewiesen.
     * 
     * @param Opus_Document $document
     */
    public function updateModel($document) {
        $roleForms = $this->getSubForms();
        
        $values = array();
        
        foreach ($roleForms as $roleForm) {
            $colForms = $roleForm->getSubForms();
            
            foreach ($colForms as $colForm) {
                $value = $colForm->getModel();
                
                if (!is_null($value)) {
                    $values[] = $value;
                }
                
            }
        }
        
       $field = $document->getField('Collection');
       
       $field->setValue($values);
    }
    
    public function continueEdit($request) {
        if ($request->getParam('continue', null) == 'addcol') {
            $colId = $request->getParam('colId');
            
            $this->_addCollection($colId);
        }
    }
    
    /**
     * Fügt Unterformular für eine Collection hinzu.
     *  
     * @param string $roleName
     * @param array $data
     * 
     * TODO Sollte roleForm nur bei Bedarf hinzufügen.
     */
    protected function _addSubForm($roleName, $data) {
        $roleForm = new Admin_Form_DocumentSection();
        
        $roleForm->setLegend('default_collection_role_' . $roleName);
        
        $position = 0;

        foreach ($data as $index => $collection) {
            $collectionForm = $this->createCollectionForm($position++);
            $collectionForm->populateFromPost($collection);
            $roleForm->addSubForm($collectionForm, $index);
        }

        $this->addSubForm($roleForm, $roleName);
    }
    
    protected function _addCollection($colId) {
        $collection = new Opus_Collection($colId);
        
        $collectionRole = $collection->getRole();
        
        $roleName = $collectionRole->getName();
        
        $roleForm = $this->_getRoleForm($roleName);
        
        $collectionForm = new Admin_Form_DocumentCollection();

        $collectionForm->populateFromModel($collection);

        $position = count($roleForm->getSubForms());
            
        $roleForm->addSubForm($collectionForm, 'collection' . $position);
    }
    
    protected function _getRoleForm($roleName) {
        $roleForm = $this->getSubForm($roleName);
        
        if (is_null($roleForm)) {
            $roleForm = new Admin_Form_DocumentSection();
            
            $roleForm->setLegend('default_collection_role_' . $roleName);
            
            $this->addSubForm($roleForm, $roleName);
        }
        
        return $roleForm;
    }
    
    public function isEmpty() {
        return count($this->getSubForms()) == 0;
    }
    
    public function createCollectionForm($position) {
        $subform = new Admin_Form_DocumentCollection();
        
        $multiWrapper = $subform->getDecorator('multiWrapper');

        if (!is_null($multiWrapper) && $multiWrapper instanceof Zend_Form_Decorator_HtmlTag) {
            $multiClass = $multiWrapper->getOption('class');
            $multiClass .= ($position % 2 == 0) ? ' even' : ' odd';
            $multiWrapper->setOption('class', $multiClass);
        }
        
        return $subform;
    }
        
}

