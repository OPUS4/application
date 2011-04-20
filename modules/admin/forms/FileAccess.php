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
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * TODO rename into Admin_Form_File (since funcionality goes beyond access
 */
class Admin_Form_FileAccess extends Admin_Form_RolesAbstract {

    protected $roleGroupLegendKey = 'admin_filemanager_legend_fileroles';

    protected $alwaysCheckAndDisableGuest = false;

    private $fileId;

    private $file;

    public function __construct($fileId) {
        $this->fileId = $fileId;
        $this->file = new Opus_File($this->fileId);
        parent::__construct();
    }

    public function init() {
        parent::init();

        $translate = Zend_Registry::get('Zend_Translate');

        $file = $this->createElement('hidden', 'FileObject');
        $this->addElement($file);

        $label = new Zend_Form_Element_Text('label');
        $label->setAttrib('size', 40);
        $label->setLabel('admin_filemanager_label_label');
        $label->setValue($this->file->getLabel());
        $this->addElement($label);

        $comment = new Zend_Form_Element_Textarea('comment');
        $comment->setAttrib('cols', 100);
        $comment->setAttrib('rows', 4);
        $comment->setLabel('admin_filemanager_label_comment');
        $comment->setValue($this->file->getComment());
        $this->addElement($comment);

        $languageList = new Zend_Form_Element_Select('language');
        $languageList->setLabel('Language')
            ->setMultiOptions(Zend_Registry::get('Available_Languages'))
            ->addValidator('NotEmpty');
        $languageList->setValue($this->file->getLanguage());
        $this->addElement($languageList);

        $frontdoorCheckbox =
                $this->createElement('checkbox', 'visibleInFrontdoor')
                     ->setLabel('admin_filemanager_label_visibleinfrontdoor');
        $frontdoorCheckbox->setValue($this->file->getVisibleInFrontdoor());
        $this->addElement($frontdoorCheckbox);


        $oaiCheckbox = $this->createElement('checkbox', 'visibleInOai')
                            ->setLabel('admin_filemanager_label_visibleinoai');
        $oaiCheckbox->setValue($this->file->getVisibleInOai());
        $this->addElement($oaiCheckbox);

        $submit = $this->createElement('submit', 'accesssubmit');
        $submit->setLabel('Save');
        $submit->setOrder(100);

        $this->addElement($submit);

        // add form elements for selecting roles
        $this->_addRolesGroup();
    }


}

?>
