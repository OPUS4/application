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
 */

/**
 * Shows a publishing form for new documents
 *
 * @category    Application
 * @package     Module_Publish
 * */
class PublishingSecond extends Zend_Form {

    public $doctype;

    public function  __construct($type, $options=null) {
        $this->doctype = $type;
        parent::__construct($options);
    }

    /**
     * Build document publishing form that depends on the doc type
     * @param $doctype
     * @return void
     */
    public function init() {

        //get the xml file for the current doctype
        $xmlFile = "../config/xmldoctypes/" . $this->doctype . ".xml";

        //create the DOM Parser for reading the xml file
        //if (!$dom = domxml_open_mem(file_get_contents($xmlFile))){
        if (!$dom = new DOMDocument()) {
            echo "Error while parsing the document\n";
            exit;
        }
        $dom->load($xmlFile);

        //parse the xml file for the tag "field"
        foreach ($dom->getElementsByTagname('field') as $field) {
            $name = $field->getAttribute('name');
            $mandatory = $field->getAttribute('mandatory');
            $type = $field->getAttribute('type');

            $formField = $this->createElement($type, $name);
            $formField->setLabel($name);
            if ($mandatory == 'yes') {
                $formField->setRequired(true);
            }
            $this->addElement($formField);
        }
       

        //Submit button
        $submit = $this->createElement('submit', 'send');
        $submit->setLabel('Send');

        $this->addElement($submit);
        
    }
   
}
