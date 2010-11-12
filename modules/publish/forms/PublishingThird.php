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
 * Selection of Collections for a document during the publishing process
 *
 * @author Susanne Gottwald
 */
class Publish_Form_PublishingThird extends Zend_Form {

    public $log;
    public $session;
    CONST SIZE = 100;

    public function __construct($options=null) {
        $this->log = Zend_Registry::get('Zend_Log');
        $this->session = new Zend_Session_Namespace('Publish');

        parent::__construct($options);
    }

    public function init() {
        $this->session->endOfCollection = false;

        if ($this->session->step == '1') {
            // get the root collections
            $top = new Zend_Form_Element_Select('collection1');
            $top->setLabel('choose_collection_role');
            $options = $this->getCollection();
            $top->setMultiOptions($options);
            $this->addElement($top);
        }
        else {

            for ($i = 2; $i <= $this->session->step; $i++) {
                //decrease
                $i = (int) $i - 1;
                if (isset($this->session->collection['collection' . $i])) {
                    //get previous collection
                    $collectionId = (int) $this->session->collection['collection' . $i];
                    $this->session->collection['collection' . $i . 'Name'] = $this->getCollectionName($collectionId);

                    //increase and get next collection
                    $i = (int) $i + 1;
                    $options = $this->getCollection($collectionId);
                    if ($options !== null) {
                        $subSelect = new Zend_Form_Element_Select('collection' . $i);
                        $subSelect->setLabel('choose_collection_subcollection');
                        $subSelect->setMultiOptions($options);
                        $this->addElement($subSelect);
                    }
                    else {
                        //end of collection tree
                        $this->session->endOfCollection = true;
                        //decrease
                        $j = $i - 1;
                        $name = 'Collection' . $this->session->countCollections;
                        $this->session->elements[$name]['name'] = $name;
                        $this->session->elements[$name]['value'] = $this->session->collection['collection' . $j];
                        $this->session->elements[$name]['label'] = 'collection';
                        $this->log->debug('Collection stored in session!');
                    }
                }
            }
        }


        if (false === $this->session->endOfCollection) {
            //the end in a tree has not been reached yet? -> go down
            //go down to child collection
            $submit = $this->createElement('submit', 'goToSubCollection');
            $submit->setLabel('button_label_choose_subcollection');
            $this->addElement($submit);
        }

        if (true === $this->session->endOfCollection) {
            //the end has been reached? -> save or choose another collection
            //choose another collection
            $submit = $this->createElement('submit', 'chooseAnotherCollection');
            $submit->setLabel('button_label_choose_another_collection');
            $this->addElement($submit);

            //save
            $submit = $this->createElement('submit', 'send');
            $submit->setLabel('button_label_send');
            $this->addElement($submit);
        }

        if ((int) $this->session->step >= 2) {
            //go up to parent collection
            $submit = $this->createElement('submit', 'goToParentCollection');
            $submit->setLabel('button_label_choose_parentcollection');
            $this->addElement($submit);
        }
    }

    /**
     * Method to fetch collections for select options.
     * @param <type> $oaiName
     * @param <type> $collectionId
     * @return array of options
     */
    protected function getCollection($collectionId=null) {
        $collections = array();

        if (false === isset($collectionId)) {
            //get top classes of collectin_role
            $roles = Opus_CollectionRole::fetchAll();

            foreach ($roles as $role) {
                if (!is_null($role->getRootCollection())) {
                    $collections[$role->getRootCollection()->getId()] = $role->getDisplayName();
                }
            }
        }
        else {
            $collection = new Opus_Collection($collectionId);
            $colls = $collection->getChildren();
            if (isset($colls) && count($colls) > 1) {
                foreach ($colls as $coll) {
                    $collections[$coll->getId()] = $coll->getDisplayName();
                }
            }
            else
                return null;
        }
        return $collections;
    }

    /**
     * Method to find oput the name for a collection id.
     * @param <Int> $collectionId
     * @return <String> name for collection id
     */
    protected function getCollectionName($collectionId = null) {
        if (isset($collectionId)) {
            $collection = new Opus_Collection($collectionId);
            $name = $collection->getDisplayName();
            if (empty($name)) {

                $name = $collection->getRoleName();
            }
            return $name;
        }
    }

    /**
     *
     * @param <type> $elementName
     * @return string
     */
    public function getElementAttributes($elementName) {
        $elementAttributes = array();
        $element = $this->getElement($elementName);

        $elementAttributes['value'] = $element->getValue();
        $elementAttributes['label'] = $element->getLabel();
        $elementAttributes['error'] = $element->getMessages();
        $elementAttributes['id'] = $element->getId();
        $elementAttributes['type'] = $element->getType();
        $elementAttributes['desc'] = $element->getDescription();
        $elementAttributes['hint'] = 'hint_' . $elementName;
        $elementAttributes['header'] = 'header_' . $elementName;
        $elementAttributes['disabled'] = $element->getAttrib('disabled');

        if ($element->getType() === 'Zend_Form_Element_Checkbox') {
            $elementAttributes['value'] = $element->getCheckedValue();
        }

        if ($element->getType() === 'Zend_Form_Element_Select') {
            $elementAttributes["options"] = $element->getMultiOptions(); //array
            $elementAttributes["selectedOption"] = $element->getMultiOption($element->getValue());
        }

        if ($element->isRequired())
            $elementAttributes["req"] = "required";
        else
            $elementAttributes["req"] = "optional";

        return $elementAttributes;
    }

}

?>
