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
 * @author      Pascal-Nicolas Becker <becker@zib.de>
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2009-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

if (false === is_null(ini_get('register_argc_argv'))
        && ini_get('register_argc_argv') == 1
        && $_SERVER['argc'] > 1) {
    $snippetFiles = $_SERVER['argv'];
    // removes script name
    array_shift($snippetFiles);
    foreach ($snippetFiles as $snippetFile) {
        if (false === file_exists($snippetFile)) {
            echo "# snippet $snippetFile does not exist\n";
            continue;
        }

        try {
            include_once($snippetFile);
            echo "# included snippet $snippetFile\n";
        } catch (Exception $e) {
            echo "# failed including snippet $snippetFile: \n";
            echo 'Caught exception ' . get_class($e) . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
            // exit here, so nobody thinks that the script was loaded.
            exit(1);
        }
    }
}
