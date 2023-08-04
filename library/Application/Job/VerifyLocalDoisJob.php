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
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Doi\DoiManager;
use Opus\Job\TaskAbstract;

/*
 * Dieses Script sucht nach Dokumenten, die lokale DOIs im Status 'registered'
 * besitzen und verifiziert diese DOIs. Die Verifikation ist erforderlich, weil
 * nach der Registrierung einer DOI bei DataCite bis zu 24-72 Stunden vergehen
 * können, bis die DOI tatsächlich auflösbar ist.
 *
 * Eine DOI im Zustand 'registered' wird erst dann in OPUS auf den Status 'verified'
 * gesetzt, wenn sie über das Handle-System tatsächlich auflösbar ist.
 *
 */
class Application_Job_VerifyLocalDoisJob extends TaskAbstract
{
    /** @var bool */
    private $printErrors = false;

    // prüfe nur lokale DOIs, die vor mindestens 24h bei DataCite registriert wurden
    // setze den Wert von $delayInHours auf null, um alle registrierten DOIs unabhängig
    // vom Registrierungszeitpunkt zu prüfen
    /** @var int */
    private $delayInHours;

    /** @param int $delayInHours */
    public function setDelayInHours($delayInHours)
    {
        $this->delayInHours = $delayInHours;
    }

    /**
     * setze auf $printErrors auf true, um Fehlermeldungen auf der Konsole auszugeben
     */
    public function enablePrintErrors()
    {
        $this->printErrors = true;
    }

    /**
     * @return int
     */
    public function run()
    {
        $output = $this->getOutput();

        $beforeDate = null;
        if ($this->delayInHours !== null) {
            $beforeDate = date("Y-m-d H:i:s", strtotime("- $this->delayInHours hours"));
        }

        $doiManager = new DoiManager();
        $status     = $doiManager->verifyRegisteredBefore($beforeDate);

        if ($status->isNoDocsToProcess()) {
            $output->writeln("could not find matching documents for DOI verification");
        } else {
            $output->writeln(count($status->getDocsWithDoiStatus()) . " documents have been processed");

            if ($this->printErrors) {
                foreach ($status->getDocsWithDoiStatus() as $docId => $docWithStatus) {
                    if ($docWithStatus['error']) {
                        $output->writeln("document $docId could not verified successfully: " . $docWithStatus['msg']);
                    }
                }
            }
        }

        return 0;
    }
}
