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
 * @package     Module_Frontdoor
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Frontdoor_Model_File {

    private $docId;
    private $filename;

    public function __construct($docId, $filename) {
        if (mb_strlen($docId) < 1 or preg_match('/^[\d]+$/', $docId) === 0) {
            throw new Exception("bad request", 400);
        }

        if (mb_strlen($filename) < 1 or preg_match('/\.\.\//', $filename) === 1) {
            throw new Exception("bad request", 400);
        }

        $this->docId = $docId;
        $this->filename = $filename;

    }

    public function getFileObject($realm) {
        $document = null;
        try {
            $document = new Opus_Document($this->docId);
        }
        catch (Opus_Model_NotFoundException $e) {
            throw new Exception("doc not found", 404);
        }

        if ($document->getServerState() === 'deleted') {
            throw new Exception("doc gone", 410);
        }

        if ($document->getServerState() !== 'published' 
                and !$this->checkDocument($this->docId, $realm)) {
            throw new Exception("doc forbidden", 403);
        }

        // lookup the target file
        $targetFile = Opus_File::fetchByDocIdPathName($this->docId, $this->filename);
        if (is_null($targetFile) === true) {
            // file not found
            throw new Exception("file not found", 404);
        }

        // check if we have access
        if (!$this->checkFile($targetFile->getId(), $realm)) {
            throw new Exception("file forbidden", 403);
        }

        return $targetFile;
    }

    function checkDocument($docId, $realm) {
        if (is_null($docId) or !($realm instanceof Opus_Security_Realm)) {
            return null;
        }
        return $realm->checkDocument($docId);
    }

    function checkFile($fileId, $realm) {
        if (is_null($fileId) or !($realm instanceof Opus_Security_Realm)) {
            return null;
        }
        return $realm->checkFile($fileId);
    }
}
