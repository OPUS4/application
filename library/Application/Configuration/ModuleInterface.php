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

/**
 * Interface for OPUS module descriptors.
 */
interface Application_Configuration_ModuleInterface
{
    /**
     * Name of the module.
     *
     * This is also the name of the folder that contains the module.
     *
     * @return string
     */
    public function getName();

    /**
     * A short description of the module.
     *
     * This is displayed underneath the module name on the modules management page.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Returns true if the module has been registered with a custom descriptor.
     *
     * @return bool
     */
    public function isRegistered();

    /**
     * Returns true if 'guest' has access to the module.
     *
     * @return bool
     */
    public function isPublic();

    /**
     * Validates if the module has been setup properly.
     *
     * @return mixed
     */
    public function validateSetup();

    /**
     * Returns true if the module can be configured.
     *
     * @return bool
     */
    public function isConfigurable();
}
