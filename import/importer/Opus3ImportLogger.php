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

 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 201, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: Opus3ImportLogger.php 8787 2011-08-03 11:03:58Z gmaiwald $
 */

class Opus3ImportLogger {

   /**
    * Holds Zend-Configurationfile
    *
    * @var file.
    */

    protected $config = null;

    /**
     * Holds the logfile for Importer
     *
     * @var string  Path to logfile
     */
    protected $logfile = null;

    /**
     * Holds the filehandle of the logfile
     *
     * @var file  Fileandle logfile
     */
    protected $_logfile;

    public function __construct() {
        $this->config = Zend_Registry::get('Zend_Config');
        $this->logfile = $this->config->import->logfile;
        try {
            $this->_logfile= @fopen($this->logfile, 'a');
            if (!$this->_logfile) {
                throw new Exception("ERROR Opus3ImportLogger: Could not create '".$this->logfile."'\n");
            }
        } catch (Exception $e){
            echo $e->getMessage();
        }
    }


    public function log_debug($class, $string) {
        $string = date('Y-m-d H:i:s') . " DEBUG " . $class . ": " . $string . "\n";
        echo $string;
        fputs($this->_logfile, $string);
    }

    public function log_error($class, $string) {
        $string = date('Y-m-d H:i:s') . " ERROR " . $class . ":" . $string . "\n";
        echo $string;
        fputs($this->_logfile, $string);
    }

    public function finalize() {
        fclose($this->_logfile);
    }
}
?>
