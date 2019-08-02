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

/**
 * Shows a publishing form for new documents
 *
 */
class Publish_Form_PublishingSecond extends Publish_Form_PublishingAbstract
{
    const FIRST = "Firstname";
    const COUNTER = "_1";
    const GROUP = "group";
    const LABEL = "_label";
    const ERROR = "Error";

    public $doctype = "";
    public $additionalFields = [];
    public $postData = [];
    public $log;

    public function __construct($log, $postData = null)
    {
        $this->postData = $postData;
        $this->log = $log;
        parent::__construct();
    }

    /**
     * Overwritten method isValid to support extended validation
     * @param <type> $data
     */
    public function isValid($data)
    {
        $extended = new Publish_Model_ExtendedValidation($this, $data, $this->log, $this->_session);
        $validExtended = $extended->validate();

        $data = $this->getValues();
        $validParent = parent::isValid($data);
        // undo changes through validation: restore values of disabled fields
        $this->populate($data);

        $validExtendedAgain = $extended->validate(); // TODO why?

        //inherit data changes during validation
        $this->populate($extended->data);
        $this->postData = $extended->data;

        return ($validExtended && $validParent && $validExtendedAgain);
    }

    /**
     * Build document publishing form whose fields depend on the choosen document type.
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        $this->setDisableTranslator(true);

        $this->doctype = $this->_session->documentType;

        if (! isset($this->doctype) or empty($this->doctype)) {
            throw new Publish_Model_FormSessionTimeoutException();
        }

        $dom = null;
        try {
            // Fetch the current XML DOM structure of the documenttype.
            $dom = $this->_documentTypesHelper->getDocument($this->doctype);
        } catch (Application_Exception $e) {
            $this->log->err("Unable to load document type '" . $this->doctype . "'");
            throw $e;
        }

        $this->additionalFields = $this->_session->additionalFields;

        $parser = new Publish_Model_DocumenttypeParser($dom, $this, $this->additionalFields, $this->postData);
        $parser->parse();
        $parserElements = $parser->getFormElements();

        $this->log->debug(
            "DocumenttypeParser (doctype '" . $this->doctype . "') found: " . count($parserElements) . " elements."
        );

        $this->addElements($parserElements);
        $this->addElements($this->getExternalElements());

        $this->addSubmitButton('button_label_send', 'send');
        $this->addSubmitButton('button_label_back', 'back');

        if (! is_null($this->postData)) {
            $this->populate($this->postData);
        }

        $this->setViewValues();
    }

    /**
     * Checks if there are external fields that belongs to the form and are not defined
     * by document type (e.g. "LegalNotices" be the View_Helper).
     * It sets important array values for these elements and returns an array of external fields.
     * @return type Array of external fields.
     */
    private function getExternalElements()
    {
        $session = new Zend_Session_Namespace('Publish');
        $externalFields = $session->DT_externals;

        // No external values found!
        if (is_null($externalFields)) {
            return [];
        }

        $externals = [];
        foreach ($externalFields as $element) {
            if (! is_null($this->getElement($element['id']))) {
                // element is already appended
                return []; // TODO besser nur Schleifendurchlauf mit 'continue' abbrechen?
            }

            // create a new element and keep the element's values in an array.
            $externalElement = $this->createElement($element['createType'], $element['id']);
            $req = ($element['req'] == 'required') ? true : false;
            $externalElement->setDisableTranslator(true)
                            ->setRequired($req)
                            ->setValue($element['value'])
                            ->setLabel($element['label'])
                            ->setAttrib('disabled', $element['disabled'])
                            ->setAttrib('DT_external', $element['DT_external'])
                            ->addErrorMessages($element['error']);
            $externals[] = $externalElement;
            $this->postData[$element['id']] = $element['value'];
        }
        return $externals;
    }

    /**
     * Prepares the form object for check view page and data storing in database.
     * It removes submit buttons, hidden fields, root nodes of browsing fields and #
     * external fields (e.g. in view helpers) from the form object.
     * Other elements are left untouched.
     * It adds two new buttons for "Back" and "Deposit Data".
     */
    public function prepareCheck()
    {
        $this->_session->elements = [];

        //iterate over form elements
        foreach ($this->getElements() as $element) {
            $name = $element->getName();
            $element->removeDecorator('Label');

            // (d) bei Collections erfolgt die Zuordnung zum Dokument nur die unterste Collection pro Gruppe
            // (e) additional externals fields (from view helpers)
            if ($element->getValue() == ""
                    || $element->getType() == "Zend_Form_Element_Submit"        // Submit buttons
                    || $element->getType() == "Zend_Form_Element_Hidden"        // Hidden fields
                    || $element->getAttrib('isRoot') == true                    // Root Nodes of Browsefields
                    || $element->getAttrib('doNotStore') == true                // (*d)
                    || (! is_null($this->_session->DT_externals))
                    && array_key_exists($element->getName(), $this->_session->DT_externals)) { // (*e)

                $this->removeElement($name);
            } else {
                // set important element values in an  array: name, value, label, datatype and subfield
                // these are used for Deposit
                $this->_session->elements[$name]['name'] = $name;
                $this->_session->elements[$name]['value'] = $element->getValue();
                $this->_session->elements[$name]['label'] = $element->getLabel();
                $this->_session->elements[$name]['datatype'] = $element->getAttrib('datatype');
                if ($element->getAttrib('subfield')) {
                    $this->_session->elements[$name]['subfield'] = '1';
                } else {
                    $this->_session->elements[$name]['subfield'] = '0';
                }
            }
        }

        $this->addSubmitButton('button_label_back', 'back');
        $this->addSubmitButton('button_label_send2', 'send');
    }

    /**
     * Set values of view variables.
     */
    public function setViewValues()
    {
        // TODO variable is not used
        $errors = $this->getMessages();

        //group fields and single fields for view placeholders
        foreach ($this->getElements() as $currentElement => $value) {
            //element names have to loose special strings for finding groups
            $name = $this->_getRawElementName($currentElement);

            if (strstr($name, 'Enrichment')) {
                $name = str_replace('Enrichment', '', $name);
            }

            //build group name
            $groupName = self::GROUP . $name;
            $this->view->$name = $name;
            $groupCount = 'num' . $groupName;

            //get the display group for the current element and build the complete group
            $displayGroup = $this->getDisplayGroup($groupName);
            if (! is_null($displayGroup)) {
                $group = $this->buildViewDisplayGroup($displayGroup);
                $group['Name'] = $groupName;
                $group['Counter'] = $this->_session->$groupCount;
                $this->view->$groupName = $group;
            }

            //single field name (for calling with helper class)
            $elementAttributes = $this->getElementAttributes($currentElement); //array

            if (strstr($currentElement, 'Enrichment')) {
                $name = str_replace('Enrichment', '', $currentElement);
                $this->view->$name = $elementAttributes;
            } else {
                $this->view->$currentElement = $elementAttributes;
            }

            $label = $currentElement . self::LABEL;
            $this->view->$label = $this->getElement($currentElement)->getLabel();
        }
    }

    /**
     * Method to find out the element name stemming.
     * @param <String> $element element name
     * @return <String> $name
     */
    private function _getRawElementName($element)
    {
        $pos = stripos($element, self::FIRST);
        if ($pos !== false) {
            //element is a person element: remove suffix "Firstname"
            return substr($element, 0, $pos);
        }

        $pos = stripos($element, self::COUNTER);
        if ($pos != false) {
            //element belongs to a group: remove suffix "_1"
            return substr($element, 0, $pos);
        }

        //"normal" element name without changes
        return $element;
    }
}
