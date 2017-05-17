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
 * Formular fuer Anzeige/Editieren einer Datei.
 *
 * @category    Application
 * @package     Admin_Form
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Admin_Form_File extends Admin_Form_AbstractModelSubForm {

    /**
     * Name fuer die Formularelemente.
     */
    const ELEMENT_ID                    = 'Id';
    const ELEMENT_FILE_LINK             = 'FileLink'; // nicht editierbar
    const ELEMENT_LABEL                 = 'Label';
    const ELEMENT_COMMENT               = 'Comment';
    const ELEMENT_MIME_TYPE             = 'MimeType'; // nicht editierbar
    const ELEMENT_LANGUAGE              = 'Language';
    const ELEMENT_FILE_SIZE             = 'FileSize'; // nicht editierbar
    const ELEMENT_VISIBILITY            = 'VisibleIn';
    const ELEMENT_SERVER_DATE_SUBMITTED = 'ServerDateSubmitted';
    const ELEMENT_SORT_ORDER            = 'SortOrder';

    /**
     * Namen der Formularelemente fuer Hashes aus der Datenbank. (nicht editierbar)
     */
    const ELEMENT_HASH_MD5              = 'HashMD5';
    const ELEMENT_HASH_SHA512           = 'HashSHA512';

    /**
     * Namen der Formularelement fuer berechnete Hashes der Datei. (nicht editierbar)
     */
    const ELEMENT_HASH_MD5_ACTUAL       = 'HashMD5Actual';
    const ELEMENT_HASH_SHA512_ACTUAL    = 'HashSHA512Actual';

    const ELEMENT_ROLES                 = 'Roles';

    const SUBFORM_HASHES                = 'Hashes';

    public function init() {
        parent::init();

        $this->setUseNameAsLabel(true);
        $this->setLabelPrefix('Opus_File_');

        $this->addElement('hidden', self::ELEMENT_ID);

        $element = $this->createElement('FileLink', self::ELEMENT_FILE_LINK);
        $element->getDecorator('ViewHelper')->setViewOnlyEnabled(true);
        $element->setLabel(null);
        $this->addElement($element);

        $element = $this->createElement('text', self::ELEMENT_FILE_SIZE);
        $element->getDecorator('ViewHelper')->setViewOnlyEnabled(true);
        $element->getDecorator('LabelNotEmpty')->setOption('disableFor', true);
        $element->setStaticViewHelper('fileSize');
        $this->addElement($element);

        $element = $this->createElement('date', self::ELEMENT_SERVER_DATE_SUBMITTED);
        $element->getDecorator('ViewHelper')->setViewOnlyEnabled(true);
        $element->setLabel('Opus_File_' . self::ELEMENT_SERVER_DATE_SUBMITTED);
        $this->addElement($element);

        $element = $this->createElement('SortOrder', self::ELEMENT_SORT_ORDER);
        $this->addElement($element);

        $this->addElement('Language', self::ELEMENT_LANGUAGE, array('label' => 'Language', 'required' => true));
        $this->addElement('text', self::ELEMENT_LABEL);
        $this->addElement('textarea', self::ELEMENT_COMMENT);

        $this->addSubForm(new Admin_Form_File_Hashes(), self::SUBFORM_HASHES);

        $this->addElement(
            'multiCheckbox', self::ELEMENT_VISIBILITY, array(
            'multiOptions' => array(
                'frontdoor' => 'admin_filemanager_label_visibleinfrontdoor',
                'oai' => 'admin_filemanager_label_visibleinoai'
            ),
            'label' => 'admin_filemanager_file_visibility'
            )
        );

        $this->addElement('Roles', self::ELEMENT_ROLES, array('label' => 'admin_filemanager_file_roles'));
    }

    /**
     * Initialisierung des Formulars mit den Werten in einer Model-Instanz.
     * @param Opus_File $file
     */
    public function populateFromModel($file) {
        $this->getElement(self::ELEMENT_ID)->setValue($file->getId());
        $this->getElement(self::ELEMENT_FILE_LINK)->setValue($file);
        $this->getElement(self::ELEMENT_LABEL)->setValue($file->getLabel());
        $this->getElement(self::ELEMENT_FILE_SIZE)->setValue($file->getFileSize());
        $this->getElement(self::ELEMENT_LANGUAGE)->setValue($file->getLanguage());
        $this->getElement(self::ELEMENT_COMMENT)->setValue($file->getComment());
        $this->getElement(self::ELEMENT_SERVER_DATE_SUBMITTED)->setValue(
            $this->getView()->formatValue()->formatDate($file->getServerDateSubmitted())
        );
        $this->getElement(self::ELEMENT_SORT_ORDER)->setValue($file->getSortOrder());

        $visibility = array();

        if ($file->getVisibleInFrontdoor()) {
            $visibility[] = 'frontdoor';
        }
        if ($file->getVisibleInOai()) {
            $visibility[] = 'oai';
        }
        $this->getElement(self::ELEMENT_VISIBILITY)->setValue($visibility);

        $this->getElement(self::ELEMENT_ROLES)->setValue($this->getRolesForFile($file->getId()));

        $this->getSubForm(self::SUBFORM_HASHES)->populateFromModel($file);
    }

    /**
     * Sets default values for form.
     *
     * @param array $post
     * @return Zend_Form
     */
    public function setDefaults(array $defaults) {
        $return = parent::setDefaults($defaults);

        if (isset($defaults[$this->getName()])) {
            $fileId = $defaults[$this->getName()][self::ELEMENT_ID];
            $file = new Opus_File($fileId);
            $this->getSubForm(self::SUBFORM_HASHES)->populateFromModel($file);
            $this->getElement(self::ELEMENT_FILE_SIZE)->setValue($file->getFileSize());
        }
        else {
            $this->getLogger()->err('No POST data for subform \'' . $this->getName() . '\'.');
        }
        return $return;
    }

    /**
     * Update einer Model-Instanz mit den Werten im Formular.
     * @param Opus_Model_AbstractDb $model
     */
    public function updateModel($file) {
        $file->setLanguage($this->getElementValue(self::ELEMENT_LANGUAGE));
        $file->setLabel($this->getElementValue(self::ELEMENT_LABEL));
        $file->setComment($this->getElementValue(self::ELEMENT_COMMENT));

        $sortOrder = $this->getElementValue(self::ELEMENT_SORT_ORDER);
        $file->setSortOrder(!is_null($sortOrder) ? $sortOrder : 0);

        $visibility = $this->getElementValue(self::ELEMENT_VISIBILITY);
        $visibility = (is_array($visibility)) ? $visibility : array($visibility);

        $file->setVisibleInFrontdoor(in_array('frontdoor', $visibility));
        $file->setVisibleInOai(in_array('oai', $visibility));

        $roles = $this->getElementValue(self::ELEMENT_ROLES);

        $this->updateFileRoles($file, $roles);
    }

    /**
     * Liefert angezeigte Datei.
     */
    function getModel() {
        $fileId = $this->getElementValue(self::ELEMENT_ID);

        if (strlen(trim($fileId)) > 0 && is_numeric($fileId)) {
            try {
                $file = new Opus_File($fileId);
            }
            catch (Opus_Model_NotFoundException $omnfe) {
                $this->getLogger()->err(__METHOD__ . " Unknown file ID = '$fileId'.");
                throw new Application_Exception("Unknown file ID = '$fileId'.");
            }

            $this->updateModel($file);

            return $file;
        }
        else {
            $this->getLogger()->err(__METHOD__ . " Bad file ID = '$fileId'.");
            throw new Application_Exception("Bad file ID = '$fileId'.");
        }

        return null;
    }

    public function getRolesForFile($fileId) {
        $checkedRoles = array();

        $roles = Opus_UserRole::getAll();

        foreach ($roles as $role) {
            $files = $role->listAccessFiles();
            if (in_array($fileId, $files)) {
                array_push($checkedRoles, $role->getName());
            }
        }

        return $checkedRoles;
    }

    public function updateFileRoles($file, $selectedRoles) {
        $selectedRoles = (is_array($selectedRoles)) ? $selectedRoles : array($selectedRoles);

        $fileId = $file->getId();

        $currentRoleNames = $this->getRolesForFile($fileId);

        // remove roles that are not selected
        foreach ($currentRoleNames as $index => $roleName) {
            if (!in_array($roleName, $selectedRoles)) {
                $role = Opus_UserRole::fetchByName($roleName);
                $role->removeAccessFile($fileId);
                $role->store();
                $this->getLogger()->debug("File ID = $fileId access for role '$roleName' removed.");
            }
        }

        if (count($selectedRoles) == 1 && is_null($selectedRoles[0])) {
            return;
        }

        // add selected roles
        foreach ($selectedRoles as $roleName) {
            $role = Opus_UserRole::fetchByName($roleName);
            if (!is_null($role)) {
                if (!in_array($roleName, $currentRoleNames)) {
                    $role->appendAccessFile($fileId);
                    $role->store();
                    $this->getLogger()->debug("File ID = $fileId access for role '$roleName' added.");
                }
                else {
                    $this->getLogger()->debug("File ID = $fileId access for role '$roleName' already permitted.");
                }
            }
            else {
                $this->getLogger()->err(__METHOD__ . " Unknown role '$roleName'.'");
            }
        }

    }

}


