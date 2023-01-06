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
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Oai_ContainerController delivers files of a document for OAI clients.
 *
 * If a document has only one file it is returned.
 *
 * If a document has multiple files that are available through OAI a TAR file
 * containing all the files is returned.
 *
 * TODO apparently cannot handle filenames with spaces
 */
class Oai_ContainerController extends Application_Controller_Action
{
    public function indexAction()
    {
        $docId = $this->getRequest()->getParam('docId', null);

        $container  = null;
        $fileHandle = null;

        try {
            $container  = new Oai_Model_Container($docId);
            $fileHandle = $container->getFileHandle();
        } catch (Application_Exception $ome) {
            $this->view->errorMessage = $ome->getMessage();
            $this->getResponse()->setHttpResponseCode(500);
            $this->render('error');
            return;
        }

        // prepare response
        $this->disableViewRendering();

        $this->getResponse()
                ->setHeader('Content-Type', $fileHandle->getMimeType(), true)
                ->setHeader(
                    'Content-Disposition',
                    'attachment; filename=' . $container->getName()
                    . $fileHandle->getExtension(),
                    true
                );

        $this->_helper->SendFile->setLogger($this->getLogger());

        try {
            $this->_helper->SendFile($fileHandle->getPath());
        } catch (Exception $ex) {
            $this->getLogger()->err($ex->getMessage());
            $this->getResponse()->clearAllHeaders();
            $this->getResponse()->clearBody();
            $this->getResponse()->setHttpResponseCode(500);
        }

        $fileHandle->delete();
    }
}
