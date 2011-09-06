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
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
abstract class Publish_Form_PublishingAbstract extends Zend_Form {

    protected $config;
    protected $session;

    public function __construct() {
        $this->session = new Zend_Session_Namespace('Publish');
        $this->config = Zend_Registry::get('Zend_Config');

        parent::__construct();
    }

    function getElementAttributes($elementName) {
        $elementAttributes = array();
        if (!is_null($this->getElement($elementName))) {
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
                if ($element->isChecked())
                    $elementAttributes['check'] = 'checked';
                else
                    $elementAttributes['check'] = '';
            }

            if ($element->getType() === 'Zend_Form_Element_Select') {
                $elementAttributes["options"] = $element->getMultiOptions(); //array
            }

            if ($element->isRequired())
                $elementAttributes["req"] = "required";
            else
                $elementAttributes["req"] = "optional";
        }

        return $elementAttributes;
    }

}
