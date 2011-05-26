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
 * @package     Module_Collection
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Remotecontrol_CollectionController extends Controller_Action {

    public function addAction() {
        $this->_helper->layout()->disableLayout();
        $request = $this->getRequest();

        $role_name = $request->getParam('role');
        $parent_number = $request->getParam('parent');
        $collection_number = $request->getParam('key');
        $collection_name = $request->getParam('title');

        try {
            $model = new Remotecontrol_Model_Collection($role_name, $parent_number);
            $model->appendChild($collection_number, $collection_name);
        }
        catch (Remotecontrol_Model_Exception $e) {
            $this->getResponse()->setHttpResponseCode(400);
            $this->view->error = $e->getMessage();
        }

    }

    public function changeTitleAction() {
        $this->_helper->layout()->disableLayout();
        $request = $this->getRequest();

        $role_name = $request->getParam('role');
        $collection_number = $request->getParam('key');
        $collection_name = $request->getParam('title');

        try {
            $model = new Remotecontrol_Model_Collection($role_name, $collection_number);
            $model->rename($collection_name);
        }
        catch (Remotecontrol_Model_Exception $e) {
            $this->getResponse()->setHttpResponseCode(400);
            $this->view->error = $e->getMessage();
        }

    }

    public function listAction() {
        $request = $this->getRequest();
        $role = $request->getParam('role');
        $number = $request->getParam('number');

        $downloadList = new Remotecontrol_Model_DownloadList();
        
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

        try {
            $this->getResponse()->setBody($downloadList->getCvsFile($role, $number));
        }
        catch (Remotecontrol_Model_Exception $e) {
            if ($e->collectionIsNotUnique()) {
                $this->getResponse()->setHttpResponseCode(501);
            }
            else {
                $this->getResponse()->setHttpResponseCode(400);
            }
            return;
        }
        $this->getResponse()->setHeader('Content-Type', 'text/plain; charset=UTF-8', true);
        $filename = $role . '_' . $number;
        $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename=' . $filename . '.csv', true);
    }

}
