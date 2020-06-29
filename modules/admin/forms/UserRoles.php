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
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Abstract class for supporting editing of Opus roles in form.
 */
class Admin_Form_UserRoles extends Application_Form_Model_Abstract
{

    const ELEMENT_ROLES = 'roles';

    protected $_roleGroupLegendKey = 'admin_form_group_roles';

    protected $_alwaysCheckAndDisableGuest = true;

    public function init()
    {
        parent::init();

        $this->setDecorators([
            'FormElements',
            'fieldset'
        ]);

        $this->removeElement(self::ELEMENT_MODEL_ID);
        $this->removeElement(self::ELEMENT_SAVE);
        $this->removeElement(self::ELEMENT_CANCEL);
        $this->removeDisplayGroup('actions');

        $this->addRoleElements();

        $this->setLegend($this->_roleGroupLegendKey);
    }

    /**
     * Adds display group for roles.
     */
    protected function addRoleElements()
    {
        $roles = Opus_UserRole::getAll();

        foreach ($roles as $role) {
            $roleName = $role->getDisplayName();
            $roleCheckbox = $this->createElement(
                'checkbox',
                $roleName
            )->setLabel($roleName);

            $this->addElement($roleCheckbox);
        }

        // TODO special code to handle role 'guest': Is that good?
        if ($this->_alwaysCheckAndDisableGuest) {
            $guest = $this->getElement('guest');
            $guest->setValue(1);
            $guest->setAttrib('disabled', true);
        }
    }

    /**
     * Initialisiert das Formular mit Werten einer Model-Instanz.
     * @param $model Opus_Account
     */
    public function populateFromModel($model)
    {
        if (! $model instanceof Opus_Account) {
            throw new Exception('Model must be of type Opus_Account');
        }

        $this->clearAll();

        $roles = $model->getRole();

        foreach ($roles as $role) {
            $name = $role->getName();
            $this->getElement($name)->setValue(1);
        }
    }

    /**
     * Set all role checkboxes to unchecked.
     *
     * TODO special handling of 'guest' (see addRoleElements() function)
     */
    public function clearAll()
    {
        $elements = $this->getElements();

        foreach ($elements as $element) {
            $element->setValue(0);
        }

        if ($this->_alwaysCheckAndDisableGuest) {
            $this->getElement('guest')->setValue(1);
        }
    }

    /**
     * Returns names of selected user roles.
     * @return array
     */
    public function getSelectedRoles()
    {
        $selected = [];

        foreach ($this->getElements() as $element) {
            if ($element->getValue() == 1) {
                $selected[] = $element->getName();
            }
        }

        return $selected;
    }

    /**
     * Aktualsiert Model-Instanz mit Werten im Formular.
     * @param $model
     */
    public function updateModel($account)
    {
        if (! $account instanceof Opus_Account) {
            throw new Exception('Model must be of type Opus_Account');
        }

        $currentUser = Zend_Auth::getInstance()->getIdentity();

        $selected = $this->getSelectedRoles();

        $roles = [];

        foreach ($selected as $name) {
            $role = Opus_UserRole::fetchByName($name);
            $roles[] = $role;
        }

        $adminRole = Opus_UserRole::fetchByName('administrator');

        if ($currentUser === $account->getLogin() && in_array($account->getId(), $adminRole->getAllAccountIds())) {
            $roles[] = $adminRole;
        }

        $account->setRole($roles);
    }
}
