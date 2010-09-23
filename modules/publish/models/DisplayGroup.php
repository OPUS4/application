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
    private $elementName;
    private $additionalFields;
    public $form;
    private $multiplicity;
    private $log;
    private $defaultNS;

    public function __construct($elementName, Publish_Form_PublishingSecond $form, $multiplicity) {
        $this->elementName = $elementName;
        $this->label = 'group' . $elementName;
        $this->form = $form;
        $this->multiplicity = $multiplicity;
        $this->log = Zend_Registry::get('Zend_Log');
        $this->defaultNS = new Zend_Session_Namespace('Publish');
    }

    public function makeDisplayGroup() {
        $displayGroup = array();
        $minNum = $this->minNumber();
        $maxNum = $this->maxNumber();

        for ($i = $minNum; $i <= $maxNum; $i++) {
            foreach ($this->elements as $element) {
                $elem = clone $element;
                $elem->setName($element->getName() . $i);                
                $this->form->addElement($elem);
                $displayGroup[] = $elem->getName();
            }
        }

        if ($maxNum > 1) {
            $deleteButton = $this->addDeleteButtonToGroup();
            $this->form->addElement($deleteButton);
            $displayGroup[] = $deleteButton->getName();
        }

        $this->defaultNS->additionalFields[$this->elementName] = $this->maxNumber();
        if ($this->maxNumber() < (int) $this->multiplicity || $this->multiplicity === '*') {
            $addButton = $this->addAddButtontoGroup();
            $this->form->addElement($addButton);
            $displayGroup[] = $addButton->getName();
        }

        $this->elements = $displayGroup;
    }

    private function maxNumber() {
        $maxNumber = 1;
        if (isset($this->additionalFields)) {
            $this->log->debug("maxNumber(): additionalFields are set!");
            if (array_key_exists($this->elementName, $this->additionalFields)) {
                //$this->log->debug("DisplayGroup -> maxNumber(): key " . $this->elementName . " exists");
                $maxNumber = (int) $this->additionalFields[$this->elementName];
                $this->log->debug("maxNumber(): key " . $this->elementName . " exists, maxnumberr = " . $maxNumber);
                //$this->log->debug("initial max number: " . $maxNumber);
            }
        }
        $this->log->debug("DisplayGroup -> maxNumber()  = " . $maxNumber);
        return $maxNumber;
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

}

?>
