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
 * @package     Module_Publish
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Publish_Model_DisplayGroup {

    public $label;
    public $elements = array(); //Array of Zend_Form_Element
    public $collectionIds = array();
    public $datatype;
            
    private $elementName;
    private $additionalFields;
    private $form;
    private $multiplicity;
    private $log;
    private $session;

    public function __construct($elementName, Publish_Form_PublishingSecond $form, $multiplicity, $log, $session) {
        $this->elementName = $elementName;

        if (strstr($elementName, 'Enrichment')) {
            $this->label = 'group' . str_replace('Enrichment', '', $elementName);
        }
        else {
            $this->label = 'group' . $elementName;
        }

        $this->form = $form;
        $this->multiplicity = $multiplicity;
        $this->log = $log;
        $this->session = $session;
    }

    /**
     * Wird für alle Group-Felder aufgerufen, die keine Collection Roles sind.
     */
    public function makeDisplayGroup() {
        $displayGroup = array();        
        $maxNum = $this->maxNumber(); // number of fieldsets for the same field type
        for ($i = 1; $i <= $maxNum; $i++) {

            foreach ($this->elements as $element) {

                $elem = clone $element;
                $elem->setDisableTranslator(true);
                $elem->setName($element->getName() . '_' . $i);                
                $this->form->addElement($elem);
                $displayGroup[] = $elem->getName();
            }
        }

        //count fields for "visually grouping" in template
        $number = count($displayGroup);
        $groupCount = 'num' . $this->label;
        if (!isset($this->session->$groupCount) || $number < $this->session->$groupCount) {
            $this->session->$groupCount = $number;
        }
        $this->log->debug("initial number for group elements = " . $number . " for group " . $this->label);        

        $buttons = $this->addDeleteButtons();
        $displayGroup = array_merge($buttons, $displayGroup);

        $this->elements = $displayGroup;
    }

    private function cloneElement($i, $currentStep) {
        $elem = clone $this->elements[0];

        $elem->setDisableTranslator(true);
        $elem->setName($this->elementName . '_' . $i);
        if (isset($this->session->additionalFields['collId1' . $this->elementName . '_' . $i])) {
            $elem->setValue($this->session->additionalFields['collId1' . $this->elementName . '_' . $i]);
        }
        if ($currentStep !== 1) { // dieser Fall tritt ein, wenn in der aktuellen Gruppe mindestens die erste Stufe ausgewählt wurde (in diesem Fall wird die erste Stufe disabled)
            $elem->setAttrib('disabled', true);
            $elem->setAttrib('isRoot', true);
        }
        $this->form->addElement($elem);        
        $this->elements[] = $elem;
        return $elem;
    }

    /**
     * Diese Funktion wird nur für CollectionRoles aufgerufen!
     */
    public function makeBrowseGroup() {        
        $displayGroup = array();
        $maxNum = $this->maxNumber(); // Anzahl der vorhandenen Gruppen für den aktuellen Collection-Typ

        for ($i = 1; $i <= $maxNum; $i++) {
            $currentStep = $this->collectionStep($i); // Anzahl der Stufen für die aktuelle Gruppe mit dem Index $i

            $selectFields = $this->browseFields($i, $currentStep);

            if (!is_array($selectFields)) {
                // es wurde noch keine Auswahl für die aktuelle Gruppe $i vorgenommen
                $element = $this->cloneElement($i, $currentStep);
                if ($i < $maxNum) { // nur die letzte Gruppe kann vom Benutzer editiert werden
                    $element->setAttrib('disabled', true);
                }
                $displayGroup[] = $element->getName();
            }
            else {
                // es wurde mindestens die erste Stufe der aktuellen Gruppe $i ausgewählt

                // die erste Stufe der Gruppe $i muss aus dem "Standard-Element" geklont werden (unschön)
                $element = $this->cloneElement($i, $currentStep);
                $rootElement = $element;
                $displayGroup[] = $element->getName();

                $numOfFields = count($selectFields);

                for ($count = 0; $count < $numOfFields; $count++) {

                    $element = $selectFields[$count];
                    $this->elements[] = $element;

                    // es muss sichergestellt werden, dass nur die unterste Stufe einer Gruppe dem Dokument zugeordnet wird
                    // alle höheren Stufen der Gruppe bekommen daher das Attribut 'doNotStore', das die Zuordnung verhindert                    
                    if ($count < $numOfFields - 2 || // mindestens zwei Stufen vor der letzten Stufe: nicht zum Dokument zuordnen
                            ($count == $numOfFields - 2 && $selectFields[$numOfFields - 1]->getAttrib('isLeaf') != true) || // die vorletzte Stufe: keine Zuordnung, wenn die letzte Stufe nicht(!) der Hinweis "Ende wurde erreicht" ist
                            ($count == $numOfFields - 1 && $element->getAttrib('isLeaf') == true)) { // die letzte Stufe: keine Zuordnung, wenn die letzte Stufe der Hinweis "Ende wurde erreicht" ist
                        $element->setAttrib('doNotStore', true);
                    }

                    // nur die letzte Select-Box der letzten Stufe darf aktiv sein (der Hinweis "Ende wurde erreicht" (erkennbar am Attribut isLeaf) darf grundsätzlich nicht disabled werden)
                    if ($i < $maxNum || ($i == $maxNum && $count < $numOfFields - 1)) {
                        if ($element->getAttrib('isLeaf') != true) {
                            $element->setAttrib('disabled', true);
                        }
                    }

                    $element->setAttrib('datatype', $this->datatype);
                    $this->form->addElement($element);
                    $displayGroup[] = $element->getName();
                }

                // Spezialbehandlung für einstufige Collection Roles: hier muss das Attribut isRoot für die erste Select-Box
                // entfernt werden, da sonst keine Zuordnung zur Collection erfolgt, wenn der "Browse Down"-Button verwendet wurde (OPUSVIER-2759)
                if ($numOfFields == 1 && $element->getAttrib('isLeaf') == true) {
                    $rootElement->setAttrib('isRoot', false);
                }
            }          
        }

        //count fields for "visually grouping" in template
        $groupCount = 'num' . $this->label;
        $this->session->$groupCount = null; // besondere Berechnung der Zebrastreifen in der View (CRs müssen speziell behandelt werden)

        $buttons = $this->addDeleteButtons();
        $displayGroup = array_merge($displayGroup, $buttons);

        $buttons = $this->browseButtons();
        if (!is_null($buttons)) {
            $displayGroup = array_merge($buttons, $displayGroup);
        }

        $this->elements = $displayGroup;
    }

    private function addDeleteButtons() {
        $displayGroup = array();        
        //show delete button only in case multiplicity has not been reached yet
        if ($this->maxNumber() < (int) $this->multiplicity || $this->multiplicity === '*') {
            $addButton = $this->addAddButtontoGroup();
            $this->form->addElement($addButton);
            $displayGroup[] = $addButton->getName();           
        }

        if ($this->maxNumber() > 1) {
            $deleteButton = $this->addDeleteButtonToGroup();
            $this->form->addElement($deleteButton);
            $displayGroup[] = $deleteButton->getName();
        }
        return $displayGroup;
    }

    /**
     * Method returns an array with one or two buttons for browsing the collections during publication.
     * The buttons have been added to the current Zend_Form.
     *
     * Wird nur für Collection Roles aufgerufen.
     *
     * @return <Array> of button names
     */
    private function browseButtons() {        
        $displayGroup = array();
        //show browseDown button only for the last select field
        $level = (int) count($this->collectionIds);
        try {
            $collection = new Opus_Collection($this->collectionIds[$level - 1]);
        }
        catch (Exception $e) {
            // TODO improve exception handling
            return null;
        }

        if ($collection->hasVisiblePublishChildren() && $collection->hasVisibleChildren()) {
            $downButton = $this->addDownButtontoGroup();
            $this->form->addElement($downButton);
            $displayGroup[] = $downButton->getName();
        }

        $isRoot = $collection->isRoot();               
        if (!$isRoot && !is_null($this->collectionIds[0])) {
            // collection has parents -> make button to browse up
            $upButton = $this->addUpButtontoGroup();
            $this->form->addElement($upButton);
            $displayGroup[] = $upButton->getName();          
        }
        
        return $displayGroup;
    }

    /**
     * Method adds different collection selection fields to the elements list of
     * the display group for the current fieldset
     *
     * @param int $fieldset Counter of the current fieldset
     * @param int $step
     */
    private function browseFields($fieldset, $step) {
        if (is_null($this->collectionIds[0])) {
            $error = $this->form->createElement('text', $this->elementName);
            $error->setLabel($this->elementName);
            $error->setDescription('hint_no_selection_' . $this->elementName);
            $error->setAttrib('disabled', true);
            $this->elements[] = $error;
            return;
        }

        if ($fieldset > 1) {
            $this->collectionIds[] = $this->collectionIds[0]; // ID der Root Collection
        }
        
        //initialize root node
        $this->session->additionalFields['collId0' . $this->elementName . '_' . $fieldset] = $this->collectionIds[0];

        if ($step < 2) { // es wurde für die aktuelle Gruppe noch keine Auswahl auf der ersten Stufe vorgenommen
            return;
        }

        $selectFields = array();

        // für die aktuelle Gruppe wurde mindestens die erste Stufe ausgewählt
        for ($j = 1; $j < $step; $j++) {
            //get the previous selection collection id from session
            if (isset($this->session->additionalFields['collId' . $j . $this->elementName . '_' . $fieldset])) {
                $id = $this->session->additionalFields['collId' . $j . $this->elementName . '_' . $fieldset];

                if (!is_null($id)) {
                    $this->collectionIds[] = $id;
                    $selectfield = $this->collectionEntries((int) $id, $j + 1, $fieldset);
                    if (!is_null($selectfield)) {
                        $selectFields[] = $selectfield;
                    }
                }
            }
        }
        return $selectFields;
    }

    private function maxNumber() {        
        if (!isset($this->additionalFields) || !array_key_exists($this->elementName, $this->additionalFields)) {
            $this->additionalFields[$this->elementName] = 1;            
        }
        return $this->additionalFields[$this->elementName];
    }

    /**
     *
     * @param int $index Index der Collection-Gruppe (beginnend bei 1)
     * @return int
     */
    private function collectionStep($index = null) {
        if (!isset($this->session->additionalFields) || !isset($this->session->additionalFields['step' . $this->elementName . '_' . $index])) {
            $this->session->additionalFields['step' . $this->elementName . '_' . $index] = 1;
        }
        return $this->session->additionalFields['step' . $this->elementName . '_' . $index];
    }

    /**
     * wird nur für Collection Roles aufgerufen
     * @param int $id ID einer Collection
     * @param int $step aktuelle Stufe innerhalb der Gruppe (>= 1)
     * @param int $fieldset aktuelle Gruppe (>= 1)
     */
    private function collectionEntries($id, $step, $fieldset) {
        try {            
            $collection = new Opus_Collection($id);
        }
        catch (Exception $e) {
            // TODO: improve exception handling!
            return null;
        }
                
        $children = array();

        if ($collection->hasChildren()) {
            $selectField = $this->form->createElement('select', 'collId' . $step . $this->elementName . '_' . $fieldset);
            $selectField->setDisableTranslator(true);
            $selectField->setLabel('choose_collection_subcollection');

            $role = $collection->getRole();
            $collsVisiblePublish = $collection->getVisiblePublishChildren();
            $collsVisible = $collection->getVisibleChildren();
            $colls = array_intersect($collsVisible, $collsVisiblePublish);
            foreach ($colls as $coll) {
                $children[] = array(
                    'key' => strval($coll->getId()),
                    'value' => $coll->getDisplayNameForBrowsingContext($role));
            }
            $selectField->setMultiOptions($children);
        }
        
        //show no field?
        if (empty($children)) {
            $selectField = $this->form->createElement('text', 'collId' . $step . $this->elementName . '_' . $fieldset);
            $selectField->setDisableTranslator(true);            
            $selectField->setLabel('endOfCollectionTree');
            $selectField->setAttrib('disabled', true);
            $selectField->setAttrib('isLeaf', true);
        }        
        return $selectField;
    }

    public function setSubFields($subFields) {
        $this->elements = $subFields;
    }

    public function setAdditionalFields($additionalFields) {
        $this->additionalFields = $additionalFields;
    }

    private function addAddButtontoGroup() {
        $addButton = $this->form->createElement('submit', 'addMore' . $this->elementName);
        $addButton->setDisableTranslator(true);
        $addButton->setLabel($this->form->view->translate('button_label_add_one_more' . $this->elementName));
        return $addButton;
    }

    private function addDeleteButtonToGroup() {
        $deleteButton = $this->form->createElement('submit', 'deleteMore' . $this->elementName);
        $deleteButton->setDisableTranslator(true);
        $deleteButton->setLabel($this->form->view->translate('button_label_delete' . $this->elementName));
        return $deleteButton;
    }

    private function addDownButtontoGroup() {
        $downButton = $this->form->createElement('submit', 'browseDown' . $this->elementName);
        $downButton->setDisableTranslator(true);
        $label = $this->form->view->translate('button_label_browse_down' . $this->elementName);
        if ($label == 'button_label_browse_down' . $this->elementName) {
            $label = $this->form->view->translate('button_label_browse_down');
        }
        $downButton->setLabel($label);
        return $downButton;
    }

    private function addUpButtontoGroup() {
        $upButton = $this->form->createElement('submit', 'browseUp' . $this->elementName);
        $upButton->setDisableTranslator(true);
        $label = $this->form->view->translate('button_label_browse_up' . $this->elementName);
        if ($label == 'button_label_browse_up' . $this->elementName) {
            $label = $this->form->view->translate('button_label_browse_up');
        }
        $upButton->setLabel($label);
        return $upButton;
    }

}

