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
 * @package     Module_Import
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
// Configure include path.
set_include_path('.' . PATH_SEPARATOR
            . PATH_SEPARATOR . dirname(__FILE__)
            . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/library'
            . PATH_SEPARATOR . get_include_path()
            . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/modules/import/models');

require_once 'Opus3Migration.php';

// command line arguments
if ($argc < 3 || in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
	
?>

This is a command line PHP script with one option.

  Usage:
  <?php echo $argv[0]; ?> <importfile> <opus 3 fulltext path> [<magic path>] [<importformat>] 

  <importfile> is the path to the file to import
  <opus 3 fulltext path> is the complete path to the fulltexts in your OPUS3 repository
  <magic path> (optional) should be supplied when the import doesnt work properly
  <importformat> (optional) is the format you wish to use (at the moment only mysqldump is supported)

<?php
} else {
    // Setup environment
    //Opus3Migration::init();

    // Start console
    $import = new Opus3Migration;
    $import->setImportfile($argv[1]);
    $import->setFulltextPath($argv[2]);
    if ($argc === 4) $import->setMagicPath($argv[3]);
    if ($argc === 5) $import->setFormat($argv[4]);
    #$import->start();
}