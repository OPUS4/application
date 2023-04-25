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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Config;

abstract class Publish_Form_PublishingAbstract extends Zend_Form
{
    /** @var Zend_Config */
    protected $config;

    /** @var Zend_Session_Namespace */
    protected $session;

    /** @var Zend_Controller_Action_Helper_Abstract */
    protected $documentTypesHelper;

    /** @var Zend_View_Interface|null */
    public $view;

    public function __construct()
    {
        $this->session             = new Zend_Session_Namespace('Publish');
        $this->config              = Config::get();
        $this->documentTypesHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes');
        $this->view                = $this->getView();
        parent::__construct();
    }

    /**
     * @param string $elementName
     * @return array
     */
    public function getElementAttributes($elementName)
    {
        $elementAttributes = [];
        if ($this->getElement($elementName) !== null) {
            $element                       = $this->getElement($elementName);
            $elementAttributes['value']    = $element->getValue();
            $elementAttributes['label']    = $element->getLabel();
            $elementAttributes['error']    = $element->getMessages();
            $elementAttributes['id']       = $element->getId();
            $elementAttributes['type']     = $element->getType();
            $elementAttributes['desc']     = $element->getDescription();
            $elementAttributes['hint']     = $this->getFieldHint($elementName);
            $elementAttributes['header']   = 'header_' . $elementName;
            $elementAttributes['disabled'] = $element->getAttrib('disabled');
            $elementAttributes['datatype'] = $element->getAttrib('datatype');

            if ($element->getType() === 'Zend_Form_Element_Checkbox') {
                $elementAttributes['value'] = $element->getCheckedValue();
                if ($element->isChecked()) {
                    $elementAttributes['check'] = 'checked';
                } else {
                    $elementAttributes['check'] = '';
                }
            }

            if ($element->getType() === 'Zend_Form_Element_Select') {
                $elementAttributes["options"] = $element->getMultiOptions(); //array
            }

            if ($element->isRequired()) {
                $elementAttributes['req'] = 'required';
            } else {
                $elementAttributes['req'] = 'optional';
            }

            if ($element->getAttrib('isLeaf')) {
                $elementAttributes['isLeaf'] = true;
            }

            if ($element->getAttrib('subfield')) {
                $elementAttributes['subfield'] = true;
            } else {
                $elementAttributes['subfield'] = false;
            }

            if ($element->getAttrib('DT_external')) {
                $elementAttributes['DT_external'] = true;
            }
        }

        return $elementAttributes;
    }

    /**
     * Method to build a display group by a number of arrays for fields, hidden fields and buttons.
     *
     * @param Zend_Form_DisplayGroup $displayGroup
     * @return array
     */
    public function buildViewDisplayGroup($displayGroup)
    {
        $groupFields  = [];
        $groupHiddens = [];
        $groupButtons = [];

        foreach ($displayGroup->getElements() as $groupElement) {
            $elementAttributes = $this->getElementAttributes($groupElement->getName());

            if ($groupElement->getType() === 'Zend_Form_Element_Submit') {
                //buttons
                $groupButtons[$elementAttributes["id"]] = $elementAttributes;
            } elseif ($groupElement->getType() === 'Zend_Form_Element_Hidden') {
                //hidden fields
                $groupHiddens[$elementAttributes["id"]] = $elementAttributes;
            } else {
                //normal fields
                $groupFields[$elementAttributes["id"]] = $elementAttributes;
            }
        }

        $group            = [];
        $group['Fields']  = $groupFields;
        $group['Hiddens'] = $groupHiddens;
        $group['Buttons'] = $groupButtons;
        return $group;
    }

    /**
     * Adds submit button to the form.
     *
     * @param string $label visible button label
     * @param string $name unique button name
     * @return Zend_Form_Element
     */
    public function addSubmitButton($label, $name)
    {
        $submit = $this->createElement('submit', $name);
        $submit->setDisableTranslator(true);
        $submit->setLabel($this->view->translate($label));
        $this->addElement($submit);
        return $submit;
    }

    /**
     * @param string $elementName
     * @return string
     */
    private function getFieldHint($elementName)
    {
        if (strpos($elementName, 'collId') === 0) {
            // Übersetzung für "collection hints": Stufennummer im Elementnamen enthalten
            // d.h. Elementname folgt dem Schema 'collId' . $level . $suffix
            $elementName = preg_replace('/^collId\d*/', '', $elementName);
        }
        $nameWithoutCounter = explode('_', $elementName);
        return 'hint_' . $nameWithoutCounter[0];
    }
}
