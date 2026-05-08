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
 * @copyright   Copyright (c) 2026, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

require_once dirname(__FILE__) . '/../common/update.php';

use Opus\Db2\Configuration;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Filesystem\Filesystem;

$output = new ConsoleOutput();

$configPath = APPLICATION_PATH . '/application/configs/config.xml';

if (! file_exists($configPath)) {
    exit();
}

$output->writeln("Importing '{$configPath}' into database.");

if (! is_readable($configPath)) {
    $output->writeln('<error>File \'' . $configPath . '\' is not readable.</error>');
    exit();
}

// Import options from config.xml
$config         = new Zend_Config_Xml($configPath);
$configDatabase = new Configuration();
$configDatabase->import($config, true);

// Show imported options
$imported = $configDatabase->getConfig();
if (count($imported) > 0) {
    $options = $configDatabase->arr2ini($imported->toArray());
    $output->writeln('Imported options:');
    foreach ($options as $key => $value) {
        $output->writeln('  ' . $key . ' = ' . $value);
    }
}

// Remove config.xml file
$helper = new Application_Update_Helper();

if ($helper->askYesNo("Delete '{$configPath}' file [Y/n]?", true)) {
    $output->write("Removeing '{$configPath}' file ... ");
    $filesystem = new Filesystem();
    $filesystem->remove($configPath);
    $output->writeln('done');
}
