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
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\UserRole;

/**
 * Default descriptor for modules.
 *
 * This descriptor is used for modules that did not register a custom descriptor in their
 * bootstrap.
 */
class Application_Configuration_Module implements Application_Configuration_ModuleInterface
{
    /**
     * Name (also folder) of module.
     *
     * @var string
     */
    private $name;

    /**
     * Short module description.
     *
     * @var string
     */
    private $description;

    /**
     * @param string      $name Name/folder of module.
     * @param string|null $description Short module description.
     */
    public function __construct($name, $description = null)
    {
        $this->name        = $name;
        $this->description = $description;
    }

    /**
     * Returns name of module.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns short description of module.
     *
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Checks if the module has been registered.
     *
     * Registration is checked by name.
     *
     * @return bool true - if the module has been registered
     */
    public function isRegistered()
    {
        return Application_Modules::getInstance()->isRegistered($this->getName());
    }

    /**
     * Checks if 'guest' user has access to module.
     *
     * @return bool true - if 'guest' user has access
     */
    public function isPublic()
    {
        $guest        = UserRole::fetchByName('guest');
        $guestModules = $guest->listAccessModules();

        return in_array($this->getName(), $guestModules);
    }

    /**
     * Validates requirments for using module.
     *
     * @return true - true if module can be used
     */
    public function validateSetup()
    {
        // TODO check requirements in descriptor
        return true;
    }

    /**
     * Returns true if the module has configurable options.
     *
     * @return false true - if module has options
     */
    public function isConfigurable()
    {
        return false;
    }
}
