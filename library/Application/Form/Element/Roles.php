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

use Opus\Common\UserRole;

/**
 * Formularelement für Auswahl von Rollen über Checkboxen.
 */
class Application_Form_Element_Roles extends Application_Form_Element_MultiCheckbox
{
    public function init()
    {
        parent::init();

        $this->addPrefixPath('Application_Form_Decorator', 'Application/Form/Decorator', Zend_Form::DECORATOR);

        $this->setMultiOptions($this->getRolesMultiOptions());
    }

    public function loadDefaultDecorators()
    {
        if (! $this->loadDefaultDecoratorsIsDisabled() && count($this->getDecorators()) === 0) {
            $this->setDecorators([
                'ViewHelper',
                'ElementHtmlTag',
                [
                    'LabelNotEmpty',
                    [
                        'tag'        => 'div',
                        'tagClass'   => 'label',
                        'placement'  => 'prepend',
                        'disableFor' => true,
                    ],
                ],
                [
                    ['dataWrapper' => 'HtmlTagWithId'],
                    ['tag' => 'div', 'class' => 'data-wrapper'],
                ],
            ]);
        }
    }

    /**
     * Create options for all roles.
     *
     * @return array
     */
    public function getRolesMultiOptions()
    {
        $roles = UserRole::getAll();

        $options = [];

        foreach ($roles as $role) {
            $roleName           = $role->getDisplayName();
            $options[$roleName] = $roleName;
        }

        return $options;
    }

    /**
     * Sets selected roles.
     *
     * @param mixed $value Role names or UserRole objects
     */
    public function setValue($value)
    {
        if (is_array($value)) {
            if (count($value) > 0 && $value[0] instanceof UserRole) {
                $value = $this->getRoleNames($value);
            }
        }

        parent::setValue($value);
    }

    /**
     * Returns array of UserRole objects.
     *
     * @return array of UserRole
     */
    public function getRoles()
    {
        $names = $this->getValue();

        $roles = [];

        if (is_array($names)) {
            foreach ($names as $name) {
                array_push($roles, UserRole::fetchByName($name));
            }
        }

        return $roles;
    }

    /**
     * Converts array with objects into array with role names.
     *
     * @param array $roles UserRole objects
     * @return string[] Role names
     */
    public function getRoleNames($roles)
    {
        $names = [];

        foreach ($roles as $role) {
            array_push($names, $role->getName());
        }

        return $names;
    }
}
