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

/**
 * Controller für die Anzeige von Informationen zur Konfiguration von OPUS und dem System auf dem es läuft.
 */
class Admin_InfoController extends Application_Controller_Action
{
    /**
     * Zeigt Informationen über die OPUS Systemkonfiguration an.
     */
    public function indexAction()
    {
        $config = $this->getConfig();

        if (isset($config->publish->maxfilesize)) {
            $this->view->maxfilesize = $config->publish->maxfilesize;
        } else {
            $this->view->maxfilesize = $this->view->translate('admin_info_error_not_set');
        }
        $this->view->postMaxSize       = ini_get('post_max_size');
        $this->view->uploadMaxFilesize = ini_get('upload_max_filesize');

        $workspace = new Application_Configuration_Workspace();
        $folders   = $workspace->getFolders();
        ksort($folders);
        $this->view->workspaceFolders = $folders;
    }

    /**
     * Zeigt an, ob eine neuere Version von OPUS verfügbar ist.
     *
     * TODO Behandlung von $latestVersion === null hängt vom Verhalten der Version Helpers ab (ueberarbeiten)
     * TODO move comparison code into non-controller class, e.g. Application_Configuration
     */
    public function updateAction()
    {
        $localVersion  = Application_Configuration::getOpusVersion();
        $latestVersion = $this->_helper->version();

        $this->view->currentVersion = $localVersion;
        $this->view->latestVersion  = $latestVersion;
        $this->view->showUpdateLink = false;

        if ($latestVersion === null) {
            $this->view->message = $this->view->translate('admin_info_version_error_getting_latest');
        } elseif ($localVersion === $latestVersion) {
            $this->view->message = $this->view->translate('admin_info_version_current');
        } else {
            if (strpos($localVersion, 'DEV') === false) {
                if (version_compare($localVersion, $latestVersion) >= 0) {
                    $this->view->message = $this->view->translate('admin_info_version_current');
                } else {
                    $this->view->message        = $this->view->translate('admin_info_version_outdated');
                    $this->view->showUpdateLink = true;
                }
            } else {
                if (version_compare(substr($localVersion, 0, 5), substr($latestVersion, 0, 5)) < 0) {
                    $this->view->message        = $this->view->translate('admin_info_version_outdated');
                    $this->view->showUpdateLink = true;
                } else {
                    $this->view->message = $this->view->translate('admin_info_version_current');
                }
            }
        }
    }
}
