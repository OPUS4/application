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
 * Controller für die Anzeige von Informationen zur Konfiguration von OPUS und dem System auf dem es läuft.
 *
 * @category    Application
 * @package     Module_Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Admin_InfoController extends Controller_Action {

    public function indexAction() {
        $this->view->info = new Admin_Model_OpusInfo();
        $config = Zend_Registry::get('Zend_Config');

        if (isset($config->publish->maxfilesize)) {
            $this->view->maxfilesize = $config->publish->maxfilesize;
        } else {
            $this->view->maxfilesize = $this->view->translate('admin_info_error_not_set');
        }
        $this->view->postMaxSize = ini_get('post_max_size');
        $this->view->uploadMaxFilesize = ini_get('upload_max_filesize');
        $this->view->versionLabel = $this->compareVersion();
    }

    private function compareVersion() {
        $this->versionFile = APPLICATION_PATH .DIRECTORY_SEPARATOR . "VERSION.txt";
        $localVersion = (file_exists($this->versionFile)) ? $version = trim(file_get_contents($this->versionFile)) : null;
        $latestVersion = 'Opus 4.4.2'; //file_get_contents('http://opus4.kobv.de/update');
        $this->view->versionUpdate = '';

        if (is_null($localVersion)) {
            return '';
        }
        if (is_null($latestVersion)) {
            return '';
        }
        if ($localVersion == $latestVersion) {
            return 'version_latest';
        }
        else {
            $this->view->versionUpdate = 'version_get_Update';
            return 'version_outdated';
        }
    }
}
