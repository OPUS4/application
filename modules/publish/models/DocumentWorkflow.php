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
 * @package     Module_Publish
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2011-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Publish_Model_DocumentWorkflow
{

    const DOCUMENT_STATE = 'temporary';

    /**
     * @var Opus_Document
     */
    private $_document;

    /**
     * Create and initialize document object.
     *
     * @param type $documentType
     * @return Opus_Document
     */
    public function createDocument($documentType)
    {
        $this->_document = new Opus_Document();
        $this->_document->setServerState(self::DOCUMENT_STATE)
            ->setType($documentType);

        $this->initializeDocument();

        return $this->_document;
    }

    /**
     * Initialize custom document fields.
     *
     * @return void
     */
    protected function initializeDocument()
    {
    }

    /**
     * Load initialized document object (and check document status).
     *
     * @param type $documentId
     * @return Opus_Document
     * @throws Publish_Model_Exception
     */
    public function loadDocument($documentId)
    {
        if (! isset($documentId) or ! preg_match('/^\d+$/', $documentId)) {
            throw new Publish_Model_Exception('Invalid document ID given');
        }

        $this->_document = new Opus_Document($documentId);
        if ($this->_document->getServerState() !== self::DOCUMENT_STATE) {
            throw new Publish_Model_Exception('Document->ServerState mismatch!');
        }

        return $this->_document;
    }

    public function getDocument()
    {
        return $this->_document;
    }
}
