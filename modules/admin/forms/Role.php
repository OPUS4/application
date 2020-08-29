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
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Form for creating and editing a role.
 */
class Admin_Form_Role extends Application_Form_Model_Abstract
{

    const ELEMENT_NAME = 'Name';

    private static $_protectedRoles = ['administrator', 'guest'];

    /**
     * Constructs form.
     * @param int $id
     */
    public function __construct($id = null)
    {
        parent::__construct();

        $section = empty($id) ? 'new' : 'edit';

        if (! empty($id)) {
            $role = new Opus_UserRole($id);
            $this->populateFromModel($role);
        }
    }

    public function init()
    {
        parent::init();

        $this->setUseNameAsLabel(true);
        $this->setModelClass('Opus_UserRole');

        $name = $this->createElement('text', self::ELEMENT_NAME, [
            'required' => true
        ]);

        $maxLength = Opus_UserRole::getFieldMaxLength('Name');

        $name->addValidator('regex', false, ['pattern' => '/^[a-z][a-z0-9]/i'])
            ->addValidator('stringLength', false, ['min' => 3, 'max' => $maxLength])
            ->addValidator(new Application_Form_Validate_RoleAvailable())
            ->setAttrib('maxlength', $maxLength);

        $name->getValidator('regex')->setMessages([
            'regexNotMatch' => 'admin_role_name_regexNotMatch'
        ]);

        $name->getValidator('stringLength')->setMessages([
            'stringLengthInvalid' => 'validation_error_stringLengthInvalid',
            'stringLengthTooShort' => 'validation_error_stringLengthTooShort',
            'stringLengthTooLong' => 'validation_error_stringLengthTooLong'
        ]);

        $this->addElement($name);
    }

    /**
     * Initialisiert das Formular mit Werten einer Model-Instanz.
     * @param $model
     */
    public function populateFromModel($role)
    {
        $this->getElement(self::ELEMENT_MODEL_ID)->setValue($role->getId());

        $nameElement = $this->getElement(self::ELEMENT_NAME);

        $roleName = $role->getName();

        $nameElement->setValue($roleName);

        if (in_array($roleName, self::$_protectedRoles)) {
            $nameElement->setAttrib('disabled', 'true');
        }
    }

    /**
     * Aktualsiert Model-Instanz mit Werten im Formular.
     * @param $model
     */
    public function updateModel($role)
    {
        $role->setName($this->getElementValue(self::ELEMENT_NAME));
    }
}
