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
 * Helper für das Auslesen der aktuellen Opusversion vom Opus-Server.
 *
 * @category    Application
 * @package     Application_Controller_Helper
 * @author      Michael Lang <lang@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Application_Controller_Action_Helper_Version extends Application_Controller_Action_Helper_Abstract {

    /**
     * Version of latest OPUS 4 Release.
     */
    private $_version;

    public function direct() {
        return $this->getVersion();
    }

    public function getVersion() {
        if (is_null($this->_version)) {
            $this->_version = $this->getLatestReleaseFromServer();
        }

        return $this->_version;
    }

    public function setVersion($version) {
        $this->_version = $version;
    }

    /**
     * Retrieves the version of the latest release from server.
     * @return string
     *
     * TODO Exception handling for connection problems (e.g. not found)
     */
    public function getLatestReleaseFromServer() {
        $versionFileContent = file_get_contents(Zend_Registry::get('Zend_Config')->update->latestVersionCheckUrl);
        $fileContentArray = explode("\n", $versionFileContent);
        return $fileContentArray[0];
    }

}