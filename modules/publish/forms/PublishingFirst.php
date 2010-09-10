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
 * Builds the fist page of an upload form for one file
 *
 */

class Publish_Form_PublishingFirst extends Zend_Form {

    public $config;

    /**
     * First publishing form of two forms
     * Here: Doctype + Upload-File
     *
     * @return void
     */
    public function init() {        
        $documentTypes = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes');

        $documentInSession = new Zend_Session_Namespace('document');
        $this->config = Zend_Registry::get('Zend_Config');

        //Select with different document types given by the used function
        $listOptions = $documentTypes->getDocumentTypes();

        if (count($listOptions)===1) {
            $value = (array_keys($listOptions));
            $doctypes = $this->createElement('text', 'type1');
            $doctypes->setLabel('selecttype')
                    ->setValue($value[0])
                    ->setAttrib('disabled', true)
                    ->setDescription('publish_controller_one_doctype');

            $doctypesHidden=$this->createElement('hidden', 'documentType');
            $doctypesHidden->setValue($value[0]);
            $this->addElement($doctypesHidden);

        }
        else {
            $doctypes = $this->createElement('select', 'documentType');
            $doctypes->setLabel('selecttype')
                    ->setMultiOptions(array_merge(array('' => 'choose_valid_doctype'), $listOptions))
                    ->setRequired(true);
        }

        // get path to store files
        $tempPath = $this->config->path->workspace->temp;
        if (true === empty($tempPath)) 
            $tempPath = '../workspace/tmp/'; // TODO defaults are in application.ini => throw exception

        // get allowed filetypes
        $filetypes = $this->config->publish->filetypes->allowed;
        if (true === empty($filetypes)) 
            $filetypes = 'pdf,txt,html,htm'; // TODO defaults are in application.ini => throw exception
        
        //get allowed file size
        $maxFileSize = (int) $this->config->publish->maxfilesize;
        if (true === empty($maxFileSize))
            $maxFileSize = 1024000; //1MB

        //get the initial number of file fields, toto: aus der config holen
        $number_of_files = (int) $this->config->form->first->numberoffiles;
        if (true === empty($number_of_files))
            $number_of_files = 1;

        //show Bibliographie?
        $bib = $this->config->form->first->bibliographie == 1;
        if (empty($bib)) {
            $bib = 0;
        }

        $fileupload = $this->createElement('File', 'fileupload');
        $fileupload->setLabel('fileupload')
                ->setRequired(false)
                ->setMultiFile($number_of_files)
                ->setDestination($tempPath)
                ->addValidator('Count', false, $number_of_files)
                ->addValidator('Size', false, $maxFileSize)     // limit to value given in application.ini
                ->addValidator('Extension', false, $filetypes)  // allowed filetypes by extension
                ->setDescription('publish_controller_index_fileupload');

        $bibliographie = null;

        if ($bib) {
            $bibliographie = $this->createElement('checkbox', 'bibliographie');
            $bibliographie->setLabel('bibliographie');
        }
        

        $submit = $this->createElement('submit', 'send');
        $submit->setLabel('Send');
    
        $this->addElements(array($doctypes, $fileupload));
        //show Bibliographie?
        if ($bib) {
            $this->addElement($bibliographie);
        }

        $this->addElement($submit);
        $this->setAttrib('enctype', Zend_Form::ENCTYPE_MULTIPART);        
    }

}