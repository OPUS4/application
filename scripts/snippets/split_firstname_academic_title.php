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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Person;

/**
 * Durchsucht die Vornamen aller in der Datenbank abgespeicherten Personen.
 * Ist in einem Vornamen auch der akademische Titel angegebenen (in Klammern),
 * dann wird dieser entfernt und in dem dafür vorgesehenen OPUS4-Feld
 * abgespeichert.
 *
 * Dieses Problem tritt auf bei der Migration aus OPUS3, wo es noch kein
 * separates Feld für das Ablegen des akademischen Titels einer Person gab.
 *
 * TODO fixing tool - where should it go?
 */

foreach (Person::getModelRepository()->getAll() as $person) {
    $firstname               = $person->getFirstName();
    $numOfOpeningParenthesis = substr_count($firstname, '(');
    $numOfClosingParenthesis = substr_count($firstname, ')');

    if ($numOfOpeningParenthesis !== $numOfClosingParenthesis) {
        // conflict found
        echo '[WARN] Opus_Person #' . $person->getId() . " with conflict in firstname '$firstname' : "
            . "mismatch between opening and closing parentheses -- skip person\n";
        continue;
    }

    if ($numOfOpeningParenthesis === 0) {
        // nothing to do
        echo '[INFO] Opus_Person #' . $person->getId()
            . " without parenthesis in firstname '$firstname' -- skip person\n";
        continue;
    }

    // check if firstname ends with '('
    if (preg_match('/^.*\)\s*$/', $firstname) === 0) {
        echo '[WARN] Opus_Person #' . $person->getId()
            . " without trailing closing parenthesis in firstname '$firstname' -- skip person\n";
        continue;
    }

    if ($numOfOpeningParenthesis > 1) {
        echo '[INFO] Opus_Person #' . $person->getId()
            . " with $numOfOpeningParenthesis parentheses in firstname '$firstname'\n";
    }

    $academicTitle = trim(strstr($firstname, '('));
    $academicTitle = trim(preg_replace('/^\((.*)\)$/', '$1', $academicTitle));
    $person->setAcademicTitle($academicTitle);

    $remainingFirstname = trim(strstr($firstname, '(', true));
    $person->setFirstName($remainingFirstname);

    try {
        $person->store();
        echo '[INFO] Opus_Person #' . $person->getId() . " changed firstname from '$firstname' "
            . "to '$remainingFirstname' and set academicTitle to '$academicTitle'\n";
    } catch (Exception $e) {
        echo '[ERR] Opus_Person #' . $person->getId() . ' could not be stored to database: ' . $e->getMessage() . "\n";
    }
}

exit();
