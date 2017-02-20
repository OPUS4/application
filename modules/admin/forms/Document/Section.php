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
 * Unterformular fuer Teilbereich der Dokument-Metadaten.
 * 
 * Diese Klasse hat die Aufgabe mehrere Unterformulare aufzunehmen und die Anzeige des Formulars zu strukturieren. 
 * 
 * TODO Parent-Class für Admin_Form_Document_MultiSubForm?
 * TODO construct and set Legend
 */
class Admin_Form_Document_Section extends Admin_Form_AbstractDocumentSubForm {
    
    public function populateFromModel($model) {
        $subforms = $this->getSubForms();
        
        foreach ($subforms as $subform) {
            $subform->populateFromModel($model);
        }
    }
    
    /**
     * 
     * @param type $post
     * @param type $document
     * 
     * TODO move to base class
     */
    public function constructFromPost($post, $document = null) {
        $subforms = $this->getSubForms();
        
        foreach ($subforms as $name => $subform) {
            if (array_key_exists($name, $post)) {
                $subform->constructFromPost($post[$name], $document);
            }
        }
    }

    public function continueEdit($request, $session = null) {
        $subforms = $this->getSubForms();
        
        foreach ($subforms as $subform) {
            $subform->continueEdit($request, $session);
        }
    }

    public function updateModel($model) {
        $subforms = $this->getSubForms();
        
        foreach ($subforms as $subform) {
            $subform->updateModel($model);
        }
    }

    /**
     * TODO redundant - look into MultSubForm as base class
     * TODO parameter is hack for OPUSVIER-3232
     */
    public function removeGapsInSubFormOrder($baseName) {
        $subforms = $this->getSubForms();

        $renamedSubforms = array();

        $pos = 0;

        foreach ($subforms as $index => $subform) {
            $subform->setOrder($pos);
            $name = $baseName . $pos;
            $renamedSubforms[$name] = $subform;
            $this->setOddEven($subform);
            $pos++;
        }

        $this->setSubForms($renamedSubforms);
    }

    /**
     * TODO redundant - look into MultSubForm as base class
     */
    public function setOddEven($subForm) {
        $position = $subForm->getOrder();

        $multiWrapper = $subForm->getDecorator('multiWrapper');

        if (!is_null($multiWrapper) && $multiWrapper instanceof Zend_Form_Decorator_HtmlTag) {
            $multiClass = $multiWrapper->getOption('class');
            $markerClass = ($position % 2 == 0) ? 'even' : 'odd';

            // TODO nicht 100% robust aber momentan ausreichend
            if (strpos($multiClass, 'even') !== false || strpos($multiClass, 'odd') !== false) {
                $multiClass = preg_replace('/odd|even/', $markerClass, $multiClass);
            }
            else {
                $multiClass .= ' ' . $markerClass;
            }

            $multiWrapper->setOption('class', $multiClass);
        }
    }

}
