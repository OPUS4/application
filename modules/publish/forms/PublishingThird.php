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
    CONST SIZE = 100;

    public function __construct($options=null) {
        $this->log = Zend_Registry::get('Zend_Log');        
        parent::__construct($options);
    }

    public function init() {
        $end = false;
        $defaultNS = new Zend_Session_Namespace('Publish');

        if ($defaultNS->step == '1') {
            $top = new Zend_Form_Element_Select('top');
            $top->setLabel('choose_collection_role');
            $options = $this->getCollection();
            $top->setMultiOptions($options);
            $this->addElement($top);
        } else {
            if (isset($defaultNS->collection['top'])) {
                $roleId = $defaultNS->collection['top'];
                $this->log->debug("roleID: " . $roleId);

                $top = $this->createElement('text', 'top');
                $top->setValue($roleId . " - " . $this->getCollectionName($roleId));
                //$top->setValue($roleId);
                $top->setAttrib('disabled', 'true');
                $top->setAttrib('size', self::SIZE);
                $this->addElement($top);

                $subText = new Zend_Form_Element_Select('sub1');
                $subText->setLabel('choose_collection_subcollection');
                $options = $this->getCollection($roleId);
                if ($options !== null) {
                    $subText->setMultiOptions($options);
                    $this->addElement($subText);
                }
                else
                    $end = true;
                

                for ($i = 2; $i < $defaultNS->step; $i++) {
                    $i = (int) $i - 1;
                    if (isset($defaultNS->collection['sub' . $i])) {
                        $collectionId = (int) $defaultNS->collection['sub' . $i];
                        $this->log->debug("collectionID : " . $collectionId);

                        $subText = $this->createElement('text', 'sub' . $i);
                        $subText->setValue($collectionId. " - " . $this->getCollectionName($collectionId));
                        //$subText->setValue($collectionId);
                        $subText->setAttrib('disabled', 'true');
                        $subText->setAttrib('size', self::SIZE);
                        $this->addElement($subText);

                        $i = (int) $i + 1;
                        $options = $this->getCollection($collectionId);
                        if ($options !== null) {
                            $subSelect = new Zend_Form_Element_Select('sub' . $i);
                            $subSelect->setLabel('choose_collection_subcollection');
                            $subSelect->setMultiOptions($options);
                            $this->addElement($subSelect);
                        }
                        else {
                            $end = true;
                            $this->log->debug("reduce i");
                            $j = $i - 1;
                            $this->log->debug("elements collection begins");
                            $defaultNS->elements['collection']['name'] = 'Collection';
                            $defaultNS->elements['collection']['value'] = $defaultNS->collection['sub' . $j];
                            $defaultNS->elements['collection']['label'] = 'collection';
                            $this->log->debug("behind elements collection");
                        }
                    }
                }
            }
        }

        if (false === $end) {
            $submit = $this->createElement('submit', 'collection');
            $submit->setLabel('button_label_choose_subcollection');
            $this->addElement($submit);
            $this->addElement($submit);
        }

        $submit = $this->createElement('submit', 'send');
        $submit->setLabel('button_label_send');
        $this->addElement($submit);
        $this->addElement($submit);
    }

    /**
     * Method to fetch collections for select options.
     * @param <type> $oaiName
     * @param <type> $collectionId
     * @return array of options
     */
    protected function getCollection($collectionId=null) {
        $collections = array();

        if (false === isset($collectionId) ) {
            //get top classes of collectin_role
            $roles = Opus_CollectionRole::fetchAll();

            foreach ($roles as $role) {
                if (!is_null($role->getRootCollection())) {
                    $collections[$role->getRootCollection()->getId()] = $role->getDisplayName();
                }
            }
        } else {
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

}

?>
