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
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Description of Group
 *
 * @author Susanne Gottwald
 */
class Publish_Model_DisplayGroup {

    public $label;
    public $elements = array(); //Array of Zend_Form_Element
    public $form;
    public $isBrowseField = false;
    public $collectionIds = array();
    public $implicitGroup = false;
    private $elementName;
    private $additionalFields;
    private $multiplicity;
    private $log;
    private $session;

    public function __construct($elementName, Publish_Form_PublishingSecond $form, $multiplicity) {
        $this->elementName = $elementName;

        if (strstr($elementName, 'Enrichment')) {
            $name = str_replace('Enrichment', '', $elementName);
            $this->label = 'group' . $name;
        }
        else
            $this->label = 'group' . $elementName;

        $this->form = $form;
        $this->multiplicity = $multiplicity;
        $this->log = Zend_Registry::get('Zend_Log');
        $this->session = new Zend_Session_Namespace('Publish');
    }

    public function makeDisplayGroup() {
        $displayGroup = array();
        $minNum = $this->minNumber();
        $maxNum = $this->maxNumber();

        //Number of fieldsets for the same field type (ex: 3 x author fieldset)
        for ($i = $minNum; $i <= $maxNum; $i++) {

            foreach ($this->elements as $element) {

                $elem = clone $element;
                $elem->setName($element->getName() . $i);
                $this->form->addElement($elem);
                $displayGroup[] = $elem->getName();
            }
        }

        //count fields for "visually grouping" in template
        $number = count($displayGroup);
        $groupCount = "num" . $this->label;
        if (!isset($this->session->$groupCount) || $number < $this->session->$groupCount)
            $this->session->$groupCount = $number;
        $this->log->debug("initial number for group elements = " . $number . " for group " . $this->label);

        $this->session->additionalFields[$this->elementName] = $this->maxNumber();

        $buttons = $this->addDeleteButtons();
        $displayGroup = array_merge($buttons, $displayGroup);

        $this->elements = $displayGroup;
    }

    public function makeBrowseGroup() {
        $displayGroup = array();
        $minNum = $this->minNumber();
        $maxNum = $this->maxNumber();

        for ($i = $minNum; $i <= $maxNum; $i++) {
            $this->session->additionalFields['step' . $this->elementName . $i] = $this->collectionStep($i);
            //update $this->collectionIds and generate fields for the current fieldset
            $currentStep = (int) $this->collectionStep($i);
            $this->browseFields($i, $currentStep);
            $allElements = count($this->elements) - 1;

            foreach ($this->elements as $count => $element) {
                if ($this->implicitGroup) {
                    //clone all elements
                    $elem = clone $element;
                    $elem->setName($element->getName() . $i);
                    $this->form->addElement($elem);
                    $displayGroup[] = $elem->getName();
                }
                else {
                    //only clone special fields
                    if ($element->getName() === $this->elementName) {
                        //clone the "root selection"
                        $elem = clone $element;
                        $elem->setName($this->elementName . $i);
                        if (isset($this->session->additionalFields['collId1' . $this->elementName . $i])) {
                            $elem->setValue('ID:' . $this->session->additionalFields['collId1' . $this->elementName . $i]);
                        }
                        if ($currentStep !== 1) {
                            //make top steps disabled
                            $elem->setAttrib('disabled', true);
                        }
                        $this->form->addElement($elem);
                        $displayGroup[] = $elem->getName();
                    }
                    else {
                        if ($count !== $allElements || $count == $allElements && $i < $maxNum) {
                            //make previous middle steps disabled
                            $element->setAttrib('disabled', true);
                        }

                        $this->form->addElement($element);
                        $displayGroup[] = $element->getName();
                    }
                }
            }
        }
        for ($i = $minNum; $i <= $maxNum; $i++) {
            $maxStep = $this->collectionStep($i);
            $name = 'collId' . $maxStep . $this->elementName . $i;
            $formElement = $this->form->getElement($name);
            if (!is_null($formElement))
                $formElement->setAttrib('disabled', false);
        }


        //count fields for "visually grouping" in template
        $groupCount = "num" . $this->label;
        $this->session->$groupCount = 100;

        $this->session->additionalFields[$this->elementName] = $maxNum;

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
     * @return <Array> of button names
     */
    private function browseButtons() {
        $displayGroup = array();
        //show browseDown button only for the last select field
        $level = (int) count($this->collectionIds);
        try {
            $collection = new Opus_Collection($this->collectionIds[$level - 1]);
        } catch (Exception $e) {
            return null;
        }
        $colls = $collection->getChildren();
        if (!is_null($colls) && count($colls) >= 1) {
            //collection has children
            $displayButton = false;
            //check all children to prevent false buttons
            foreach ($colls AS $coll) {
                $childs = $coll->getChildren();
                if (!is_null($childs) && count($childs) >= 1)
                    $displayButton = true;
            }
            if ($displayButton) {
                //Collection has at least one child with children -> make button to browse down
                $downButton = $this->addDownButtontoGroup();
                $this->form->addElement($downButton);
                $displayGroup[] = $downButton->getName();
            }
        }

        $isRoot = $collection->isRoot();       
        if (!$isRoot && !is_null($this->collectionIds[0])) {
            //Collection has parents -> make button to browse up
            $upButton = $this->addUpButtontoGroup();
            $this->form->addElement($upButton);
            $displayGroup[] = $upButton->getName();
        }

        return $displayGroup;
    }

    /**
     * Method adds different collection selection fields to the elements list of the disyplay group for the current fieldset
     * @param <Int> $fieldset Counter of the current fieldset
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
        if ($fieldset > 1)
            $this->collectionIds[] = $this->collectionIds[0];
        $this->session->additionalFields['collId0' . $this->elementName . $fieldset] = $this->collectionIds[0];
        $elements = array();
        //found collection level for the current fieldset        
        for ($j = 2; $j <= $step; $j++) {
            $prev = (int) $j - 1;
            //get the previous selection collection id from session
            if (isset($this->session->additionalFields['collId' . $prev . $this->elementName . $fieldset])) {
                $id = $this->session->additionalFields['collId' . $prev . $this->elementName . $fieldset];

                if ($id != '0' || !is_null($id)) {
                    //insert to array and geneerate field
                    $this->collectionIds[] = $id;
                    $selectfield = $this->collectionEntries((int) $id, $j, $fieldset);
                    if (!is_null($selectfield))
                        $this->elements[] = $selectfield;
                }
            }
        }
    }

    private function maxNumber() {
        $maxNumber = 1;
        if (isset($this->additionalFields)) {
            if (array_key_exists($this->elementName, $this->additionalFields)) {
                $maxNumber = (int) $this->additionalFields[$this->elementName];
            }
        }
        return $maxNumber;
    }

    private function collectionStep($max=null) {
        $step = 1;
        if (isset($this->session->additionalFields)) {
            if (isset($this->session->additionalFields['step' . $this->elementName . $max])) {
                $step = (int) $this->session->additionalFields['step' . $this->elementName . $max];
            }
        }
        return $step;
    }

    private function collectionEntries($id, $step, $fieldset) {
        try {
            $collection = new Opus_Collection($id);
        } catch (Exception $e) {
            return null;
        }
        $colls = $collection->getChildren();

        if (!is_null($colls) && count($colls) >= 1) {
            $selectField = $this->form->createElement('select', 'collId' . $step . $this->elementName . $fieldset);
            $selectField->setLabel('choose_collection_subcollection');
            $children = array();
            foreach ($colls as $coll) {
                $children['ID:' . $coll->getId()] = $coll->getDisplayName();
            }
            $selectField->setMultiOptions($children);
        }
        else {
            $selectField = $this->form->createElement('text', 'collId' . $step . $this->elementName . $fieldset);
            $selectField->setLabel('endOfCollectionTree');
            $selectField->setAttrib('disabled', true);
            $this->session->endOfCollectionTree['collId' . $step . $this->elementName] = 1;
        }
        return $selectField;
    }

    private function minNumber() {
        $minNumber = 1;
        return $minNumber;
    }

    public function getGroupLabel() {
        return $this->label;
    }

    public function getGroupElements() {
        if (isset($this->elements))
            return $this->elements;
        else
            return false;
    }

    public function setSubFields($subFields) {
        $this->elements = array_merge($this->elements, $subFields);
    }

    public function setAdditionalFields($additionalFields) {
        $this->additionalFields = $additionalFields;
    }

    private function addAddButtontoGroup() {
        $addButton = $this->form->createElement('submit', 'addMore' . $this->elementName);
        $addButton->setLabel('button_label_add_one_more' . $this->elementName);
        return $addButton;
    }

    private function addDeleteButtonToGroup() {
        $deleteButton = $this->form->createElement('submit', 'deleteMore' . $this->elementName);
        $deleteButton->setLabel('button_label_delete' . $this->elementName);
        return $deleteButton;
    }

    private function addDownButtontoGroup() {
        $downButton = $this->form->createElement('submit', 'browseDown' . $this->elementName);
        $downButton->setLabel('button_label_browse_down' . $this->elementName);
        return $downButton;
    }

    private function addUpButtontoGroup() {
        $upButton = $this->form->createElement('submit', 'browseUp' . $this->elementName);
        $upButton->setLabel('button_label_browse_up' . $this->elementName);
        return $upButton;
    }

}

?>
