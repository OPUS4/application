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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Unterformular für Actionbox für Metadaten-Formular.
 * 
 * Die Actiobox zeigt wichtige Statusinformationen zu einem Dokument und bietet
 * Navigation und direkten Zugang zu Funktionen wie Speichern und Abbrechen.
 */
class Admin_Form_ActionBox extends Admin_Form_AbstractDocumentSubForm {
    
    const ELEMENT_SAVE = 'Save';
    
    const ELEMENT_CANCEL = 'Cancel';
    
    const MODE_EDIT = 'edit';
    
    const MODE_VIEW = 'view';
    
    private $document;
    
    private $mode = self::MODE_EDIT;
    
    private $parentForm;
    
    public function __construct($parentForm, $options = null) {
        parent::__construct($options);
        $this->parentForm = $parentForm;
    }

    public function init() {
        parent::init();
        
        $element = new Zend_Form_Element_Submit(self::ELEMENT_SAVE);
        $element->setValue('save');
        $element->removeDecorator('DtDdWrapper');
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Submit(self::ELEMENT_CANCEL);
        $element->setValue('cancel');
        $element->removeDecorator('DtDdWrapper');
        $this->addElement($element);
    }
    
    public function populateFromModel($document) {
        $this->document= $document;
    }
    
    public function constructFromPost($post, $document = null) {
        $this->document = $document;
    }
    
    public function processPost($post, $context) {
        if (array_key_exists(self::ELEMENT_SAVE, $post)) {
            return Admin_Form_Document::RESULT_SAVE;
        }
        else if (array_key_exists(self::ELEMENT_CANCEL, $post)) {
            return Admin_Form_Document::RESULT_CANCEL;
        }
    }
    
    public function getDocument() {
        return $this->document;
    }
    
    public function getMode() {
        return $this->mode;
    }
    
    /**
     * 
     * @return string
     */
    public function getJumpLinks() {
        $links = array();
        
        if ($this->parentForm != null) {
            $subforms = $this->parentForm->getSubForms();
            
            foreach ($subforms as $name => $subform) {
                if (!is_null($subform->getDecorator('Fieldset'))) {
                    // Unterformular mit Fieldset
                    $legend = $subform->getLegend();
                    if (!is_null($legend)) {
                        $links['#fieldset-' . $name] = $legend;
                    }
                }
            }
        }
        else {
            // Sollte niemals passieren
            Zend_Registry::get('Zend_Log')->err('ActionBox without parent form');
        }
        
        return $links;
    }
    
    public function getStateLinks() {
        $links = array();
        
        $workflow = Zend_Controller_Action_HelperBroker::getStaticHelper('workflow');
        
        $targetStates = $workflow->getAllowedTargetStatesForDocument($this->document);

        foreach ($targetStates as $targetState) {
            $links[$targetState] = array(
                'module'     => 'admin',
                'controller' => 'workflow',
                'action'     => 'changestate',
                'docId'      => $this->document->getId(),
                'targetState' => $targetState
            );
        }
        
        return $links;
    }
    
    public function getViewActionLinks() {
        $actions = array();

        $docId = $this->document->getId();
        
        $actions['admin_document_edit'] = array(
            'module' => 'admin', 
            'controller' => 'document', 
            'action' => 'edit', 
            'id' => $docId
        );
        
        $actions['admin_document_files'] = array(
            'module'     => 'admin',
            'controller' => 'filemanager',
            'action'     => 'index',
            'docId'      => $docId,
        );

        $actions['admin_documents_open_frontdoor'] = array(
            'module'     => 'frontdoor',
            'controller' => 'index',
            'action'     => 'index',
            'docId'      => $docId,
        );
        
        return $actions;
    }
    
    public function loadDefaultDecorators() {
        $this->setDecorators(array(array(
            'ViewScript', array('viewScript' => 'actionbox.phtml'))));
    }
    
    public function prepareRenderingAsView() {
        $this->mode = self::MODE_VIEW;
    }
    
}
