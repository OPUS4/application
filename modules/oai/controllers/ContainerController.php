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
 * @package     Module_Oai
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2009 - 2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Oai_ContainerController extends Controller_Action {

    public function indexAction() {
        $docId = $this->getRequest()->getParam('docId', null);

        $container = null;
        $tarball = null;
        try {
            $container = new Oai_Model_Container($docId, $this->_logger);
            $tarball = $container->getTar();
        }
        catch (Oai_Model_Exception $e) {
            $this->view->errorMessage = $e->getMessage();
            $this->getResponse()->setHttpResponseCode(500);
            return $this->render('error');
        }

        // prepare response
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $this->getResponse()
                ->setHeader('Content-Type', 'application/x-tar', true)
                ->setHeader('Content-Disposition', 'attachment; filename=' . $container->getName() . '.tar', true);

        $this->_helper->SendFile->setLogger($this->_logger);
        try {
            $this->_helper->SendFile($tarball);
            $container->deleteContainer($tarball);
        } catch (Exception $e) {
            $this->_logger->err($e->getMessage());
            $this->getResponse()->clearAllHeaders();
            $this->getResponse()->clearBody();
            $this->getResponse()->setHttpResponseCode(500);
        }
    }

}