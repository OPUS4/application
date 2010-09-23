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
    public $defaultNS;

    public function __construct($options=null) {
        $this->log = Zend_Registry::get('Zend_Log');
        $this->defaultNS = new Zend_Session_Namespace('Publish');
        parent::__construct($options);
    }

    public function init() {

        if ($this->defaultNS->step == '1') {
            $top = new Zend_Form_Element_Select('top');
            $top->setLabel('choose_collection_role');
            $options = $this->getCollection();
            $top->setMultiOptions($options);
            $this->addElement($top);
            $this->defaultNS->step = '2';
        } else {
            if (isset($this->defaultNS->documentData['top'])) {
                

                $roleId = $this->defaultNS->documentData['top'];
                $this->log->debug("roleID: " . $roleId);
                $role = Opus_Collection::fetchCollectionsByRoleId($roleId);

                $top = $this->createElement('text', 'top');
                $top->setValue($role[0]->getDisplayName());
                $top->setAttrib('disabled', 'true');
                $this->addElement($top);
            }
        }

        $submit = $this->createElement('submit', 'collection');
        $submit->setLabel('button_label_choose_subcollection');
        $this->addElement($submit);
        $this->addElement($submit);

        $submit = $this->createElement('submit', 'send');
        $submit->setLabel('button_label_send');
        $this->addElement($submit);
        $this->addElement($submit);
    }

    /**
     * Method to fetch collections for select options.
     * @param <type> $oaiName
     * @param <type> $roleId
     * @return array of options
     */
    protected function getCollection($roleId=null) {
        $collections = array();
        if (false === isset($roleId)) {
            //get top classes of collectin_role
            $roles = Opus_CollectionRole::fetchAll();

            foreach ($roles as $role) {
                $collections[] = $role->getDisplayName();
            }
        } else {
            //get collections for special roleID
            $colls = Opus_Collection::fetchCollectionsByRoleId($role->getId());

            foreach ($colls as $coll) {
                $collections[] = $coll->getDisplayName();
            }
        }
        return $collections;
    }

}

?>
