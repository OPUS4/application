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
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

require_once dirname(__FILE__) . '/../common/bootstrap.php';

/*
 * Dieses Script sucht nach Dokumenten im ServerState 'published',
 * die lokale DOIs besitzen, die noch nicht bei DataCite registiert wurden.
 * Nicht registrierte DOIs sind am Statuswert 'null' erkennbar.
 *
 * Für die ermittelten DOIs wird die Registrierung bei DataCite versucht.
 *
 */

// setze auf $printErrors auf true, um Fehlermeldungen auf der Konsole auszugeben
$printErrors = false;

$doiManager = new Opus_Doi_DoiManager();
$status = $doiManager->registerPending();

if ($status->isNoDocsToProcess()) {
    echo "could not find matching documents for DOI registration\n";
}
else {
    echo count($status->getDocsWithDoiStatus()) . " documents have been processed\n";

    if ($printErrors) {
        foreach ($status->getDocsWithDoiStatus() as $docId => $docWithStatus) {
            if ($docWithStatus['error']) {
                echo "document $docId could not registered successfully: " . $docWithStatus['msg'] . "\n";
            }
        }
    }
}


