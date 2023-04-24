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
 * @copyright   Copyright (c) 2022, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Config;
use Opus\Common\Document;
use Opus\Common\Identifier;
use Opus\Common\Repository;

/**
 * Provides REST-API for OPUS 4.
 *
 * This is the start for providing RESTful services for OPUS 4. The module
 * for this controller will likely change later.
 *
 * TODO LAMINAS Die einzelnen API Funktionen sollten in separate Klassen ausgelagert werden.
 * TODO Move controller into separate module
 */
class ApiController extends Application_Controller_Action
{
    public function init()
    {
        parent::init();
        $this->disableViewRendering();
        $this->getResponse()->setHeader('Content-Type', 'application/json');
    }

    /**
     * Always allow access to this controller; Override check in parent method.
     */
    protected function checkAccessModulePermissions()
    {
    }

    /**
     * Provides list of active document types.
     *
     * TODO TEST no tests yet
     */
    public function doctypesAction()
    {
        $doctypes = array_keys($this->getHelper('documentTypes')->getDocumentTypes());

        $response = [
            'doctypes' => $doctypes,
        ];

        echo json_encode($response);
    }

    /**
     * Checks if DOI is already present in repository.
     *
     * If a published document with the DOI exists, its document ID will be returned.
     */
    public function doicheckAction()
    {
        $doi = $this->getParam('doi');

        if ($doi === null) {
            // TODO return error message
            echo json_encode([]);
            return;
        }

        $response        = [];
        $response['doi'] = $doi;
        $doiExists       = false;

        $identifier = Identifier::new();
        $identifier->setType('doi');
        $identifier->setValue($doi);

        if (! $identifier->isDoiUnique()) {
            $doiExists = true;

            $finder = Repository::getInstance()->getDocumentFinder();
            $finder->setIdentifierValue('doi', $doi);
            $documentIds = $finder->getIds();

            // there should be only one document with this DOI
            // TODO handling multiple occurances?
            $doc = Document::get($documentIds[0]);

            if ($doc->getServerState() === Document::STATE_PUBLISHED) {
                $response['docId'] = $doc->getId();
            }
        }

        $response['doiExists'] = $doiExists;

        echo json_encode($response);
    }

    /**
     * @throws Zend_Http_Client_Exception
     *
     * TODO Crossref should be just one possible data source for pre-populating the publish form
     * TODO data should be processed on server -> simplified format for Javascript code that is source independent
     */
    public function crossrefAction()
    {
        $doi = $this->getParam('doi');

        if ($doi === null) {
            // TODO return error message
            echo json_encode([]);
            return;
        }

        $config = Config::get();

        $baseUrl = rtrim($config->crossref->url, '/');
        $mailTo  = $config->crossref->mailTo;

        $crossrefUrl = $baseUrl . "/$doi";

        if (strlen(trim($mailTo)) > 0) {
            $crossrefUrl .= "?mailto=$mailTo";
        }

        $client   = new Zend_Http_Client($crossrefUrl);
        $response = $client->request(Zend_Http_Client::GET);

        // TODO error handling?
        // TODO processing of response and conversion to OPUS 4 data structure

        echo $response->getBody();
    }
}
