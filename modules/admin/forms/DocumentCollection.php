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
 * Unterformular fuer eine zugewiesene Collection.
 * 
 */
class Admin_Form_DocumentCollection extends Admin_Form_AbstractDocumentSubForm {
    
    const ELEMENT_ID = 'Id';
    
    const ELEMENT_EDIT = 'Edit';
    
    const ELEMENT_REMOVE = 'Remove';
        
    public function init() {
        parent::init();
        
        $element = new Form_Element_Hidden(self::ELEMENT_ID);
        $this->addElement($element);
        
        $element = new Form_Element_Submit(self::ELEMENT_EDIT);
        $this->addElement($element);

        $element = new Form_Element_Submit(self::ELEMENT_REMOVE);
        $this->addElement($element);
    }
    
    public function populateFromModel($collection) {
        $this->getElement(self::ELEMENT_ID)->setValue($collection->getId());
        $this->setLegend($collection->getDisplayName());
    }

    public function processPost($data, $context) {
        if (array_key_exists(self::ELEMENT_REMOVE, $data)) {
            return 'remove';
        }
        else if (array_key_exists(self::ELEMENT_EDIT, $data)) {
            // TODO edit collection (neue zuweisen, alte entfernen)
            // TODO Seitenwechel, POST sichern, Return value
            return 'edit';
        }
    }
    
    public function getModel() {
        $colId = $this->getElement(self::ELEMENT_ID)->getValue();
        
        return new Opus_Collection($colId);
    }
    
    /**
     * 
     * @param type $post
     * 
     * TODO catch bad POST
     */
    public function populateFromPost($post) {
        $colId = $post['Id'];
        $collection = new Opus_Collection($colId);
        $this->populateFromModel($collection);
    }
    
    public function loadDefaultDecorators() {
        $this->setDecorators(array(array(
            'ViewScript', array('viewScript' => 'form/collectionForm.phtml'))));
    }
    
            
}
