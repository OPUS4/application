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
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Document;
use Opus\Common\Repository;
use Opus\Doi\DoiManager;

class Admin_Model_DoiReport
{
    /** @var string|null */
    private $filter;

    /**
     * @param string|null $filter
     */
    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    /**
     * Gibt eine Liste mit Elementen vom Typ Admin_Model_DoiStatus zurück
     * (OPUS-Dokumente mit ihren zugehörigen DOIs).
     *
     * @return array von Elementen vom Typ Admin_Model_DoiStatus
     */
    public function getDocList()
    {
        $result = [];

        $doiManager = new DoiManager();
        $docs       = $doiManager->getAll($this->filter);

        foreach ($docs as $doc) {
            $dois = $doc->getIdentifierDoi();
            if (empty($dois)) {
                continue;
            }
            $doiStatus = new Admin_Model_DoiStatus($doc, $dois[0]);
            $result[]  = $doiStatus;
        }

        return $result;
    }

    /**
     * Prüft, ob der Button zum Registrieren von DOIs auf der Übersichtsseite angezeigt werden soll.
     * Dazu muss mindestens eine registrierbare lokale DOI existieren.
     *
     * Die Methode gibt hierzu die Anzahl der lokalen DOIs zurück, die noch nicht registriert wurden.
     *
     * @return int
     */
    public function getNumDoisForBulkRegistration()
    {
        $result = 0;

        $docFinder = Repository::getInstance()->getDocumentFinder();
        $docFinder->setServerState('published');
        $docFinder->setIdentifierExists('doi');
        foreach ($docFinder->getIds() as $docId) {
            $doc  = Document::get($docId);
            $dois = $doc->getIdentifierDoi();
            if ($dois !== null && ! empty($dois)) {
                // es wird nur die erste DOI für die DOI-Registrierung berücksichtigt
                $doi = $dois[0];
                if ($doi->getStatus() === null && $doi->isLocalDoi()) {
                    $result++;
                }
            }
        }
        return $result;
    }

    /**
     * Prüft, ob der Button zur Prüfung von registrierten DOIs auf der Übersichtsseite angezeigt werden soll.
     * Dazu muss mindestens ein freigeschaltetes Dokument existieren, das eine registrierte
     * DOI besitzt, die aber noch nicht geprüft wurde.
     *
     * Die Methode gibt die Anzahl der registrierten, aber noch nicht geprüften DOIs zurück.
     *
     * @return int
     */
    public function getNumDoisForBulkVerification()
    {
        $result = 0;

        $docFinder = Repository::getInstance()->getDocumentFinder();
        $docFinder->setServerState('published');
        $docFinder->setIdentifierExists('doi');

        foreach ($docFinder->getIds() as $docId) {
            $doc  = Document::get($docId);
            $dois = $doc->getIdentifierDoi();
            if ($dois !== null && ! empty($dois)) {
                // es wird nur die erste DOI für die DOI-Prüfung berücksichtigt
                $doi    = $dois[0];
                $status = $doi->getStatus();
                if ($status !== null && $status !== 'verified') {
                    $result++;
                }
            }
        }

        return $result;
    }
}
