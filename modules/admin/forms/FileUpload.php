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
 * @author      Henning Gerhardt (henning.gerhardt@slub-dresden.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Shows a upload form for one or more files
 *
 * @category    Application
 * @package     Module_Publish
 * */
class FileUpload extends Zend_Form {

    /**
     * Build easy upload form
     *
     * @return void
     */
    public function init() {

        //$this->addElement('hash', 'UploadHash', array('salt' => 'unique'));

        // FIXME: Make hard coded path configurable.
        $fileupload = new Zend_Form_Element_File('fileupload');
        $fileupload->setLabel('FileToUpload')
            ->addValidator('Count', false, 1)     // ensure only 1 file
            ->addValidator('Size', false, 102400000) // limit to 100M
            ->addValidator('Extension', false, 'pdf,txt'); // only PDF

        $comment = new Zend_Form_Element_Text('comment');
        $comment->setLabel('Comment');

        $languageList = new Zend_Form_Element_Select('language');
        $languageList->setLabel('Language')
            ->setMultiOptions(Zend_Registry::get('Available_Languages'))
            ->addValidator('NotEmpty');

        $submit = new Zend_Form_Element_Submit('uploadsubmit');
        $submit->setLabel('Process');

        $documentId = new Zend_Form_Element_Hidden('DocumentId');
        $documentId->addValidator('NotEmpty')
            ->addValidator('Int');

        $this->addElements(array($fileupload, $comment, $languageList, $documentId, $submit));
        $this->setAttrib('enctype', Zend_Form::ENCTYPE_MULTIPART);
    }
}
