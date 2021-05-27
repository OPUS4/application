<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Bibtex\Import\Console\Helper\BibtexImportHelper;
use Opus\Bibtex\Import\Console\Helper\BibtexImportResult;

class Admin_ImportController extends Application_Controller_Action
{

    public function bibtexAction()
    {
        $this->view->title = 'admin_import_bibtex';
        $request = $this->getRequest();
        $form = new Admin_Form_Import();
        if ($request->isPost()) {
            $postData = $this->getRequest()->getPost();

            if (! $form->isValid($postData)) {
                // ['module' => 'admin', 'controller' => 'import', 'action' => 'bibtex'],
                $this->_helper->Redirector->redirectTo(
                    'bibtex',
                    ['failure' => 'missing file'],
                    'import',
                    'admin'
                );
                return;
            }

            $upload = new \Zend_File_Transfer_Adapter_Http();
            $files = $upload->getFileInfo();

            if (count($files) > 0) {
                $bibtexFile = $files['File'];
                if (isset($bibtexFile['tmp_name'])) {
                    $bibtexImportResult = new BibtexImportResult();

                    $bibtexImportHelper = $this->createBibtexImportHelper($bibtexFile['tmp_name'], $postData);
                    $bibtexImportHelper->doImport($bibtexImportResult);

                    $this->view->importResult = $bibtexImportResult->getMessages();
                    $this->view->numDocsImported = $bibtexImportResult->getNumDocsImported();
                    $this->view->numDocsProcessed = $bibtexImportResult->getNumDocsProcessed();
                }
            } else {
                // POST-Request ist zwar gültig; allerdings konnte keine Datei ausgelesen werden
                $this->_helper->Redirector->redirectTo(
                    'bibtex',
                    ['failure' => 'missing file'],
                    'import',
                    'admin'
                );
                return;
            }
        } else {
            // show upload form
            $this->view->form = $form;
        }
    }

    private function createBibtexImportHelper($fileName, $postData)
    {
        $bibtexImportHelper = new BibtexImportHelper($fileName);
        if (array_key_exists(Admin_Form_Import::ELEMENT_INI_FILENAME, $postData)) {
            $bibtexImportHelper->setIniFileName($postData[Admin_Form_Import::ELEMENT_INI_FILENAME]);
        }

        if (array_key_exists(Admin_Form_Import::ELEMENT_MAPPING_NAME, $postData)) {
            $bibtexImportHelper->setMappingConfiguration($postData[Admin_Form_Import::ELEMENT_MAPPING_NAME]);
        }

        if (array_key_exists(Admin_Form_Import::ELEMENT_COLLECTION_IDS, $postData)) {
            $bibtexImportHelper->setCollectionIds($postData[Admin_Form_Import::ELEMENT_COLLECTION_IDS]);
        }

        if (array_key_exists(Admin_Form_Import::ELEMENT_VERBOSE, $postData) && $postData[Admin_Form_Import::ELEMENT_VERBOSE] === '1') {
            $bibtexImportHelper->enableVerbose();
        }

        if (array_key_exists(Admin_Form_Import::ELEMENT_DRY_MODE, $postData) && $postData[Admin_Form_Import::ELEMENT_DRY_MODE] === '1') {
            $bibtexImportHelper->enableDryMode();
        }

        return $bibtexImportHelper;
    }
}