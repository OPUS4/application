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
 * @category    Tests
 * @author      Ralf Claussnitzer <ralf.claussnitzer@slub-dresden.de>
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * This class provides a static initializiation method for setting up
 * a test environment including php include path, configuration and
 * database setup.
 *
 * @category    Tests
 */
class TestHelper extends Application_Bootstrap {

    /**
     * Set up database connection. If stored schema version information
     * denotes an deprecated schema, end with exception.
     *
     * @throws Exception Thrown if database schema check unveils an revision number mismatch.
     * @return void
     */
    protected function _initDatabase() {
        parent::_initDatabase();

        $log = $this->getResource('Logging');

        // Determine current schema revision from opus4schema.sql
        $sqlFile = dirname(dirname(__FILE__)) . '/db/schema/opus4schema.sql';
        if (false === is_file($sqlFile)) {
            $log->warn('Schema file ' . $sqlFile . ' not found.');
            return;
        }

        // Scan for revision information
        $handle = @fopen($sqlFile, 'r');
        if (false === $handle) {
            $log->warn('Cannot open schema file ' . $sqlFile . '.');
            return;
        }
        while(false === feof($handle)) {
            $line = fgets($handle);
            // Replaced Rev by [R][e][v] to avoid substitution of SVN keywords!
            if (1 === preg_match('/\$[R][e][v]: \d*\s\$/', $line, $matches)) {
                $sqlRev = $matches[0];
                break;
            }
        }
        fclose($handle);

        // Load revision from database
        $dba = Zend_Registry::get('db_adapter');
        try {
            $row = $dba->fetchRow($dba->select()->from('schema_version'));
            if (true === empty($row)) {
                throw new Exception('No revision information available.');
            }
            $dbRev = $row['revision'];
        } catch (Exception $ex) {
            $log->warn('Cannot read schema information from database: ' . $ex->getMessage());
            return;
        }

        // Compare revisions and throw exception if needed
        if (isset($sqlRev, $dbRev) && $sqlRev !== $dbRev) {
            throw new Exception("Database schema revision mismatch. SQL file has '$sqlRev', DB has '$dbRev'. Consider rebuilding the database.\n");
        }
    }

    /**
     * Use the standard database adapter to remove all records from
     * a table.
     *
     * @param string $tablename Name of the table to be cleared.
     * @return void
     */
    public static function clearTable($tablename) {
        $adapter = Zend_Db_Table::getDefaultAdapter();
        $tablename = $adapter->quoteIdentifier($tablename);
        $adapter->query("DELETE FROM $tablename");
    }

    /**
     * Returns true if the underlying operating system is Microsoft Windows (TM).
     *
     * @return boolean True in case of MS Windows; False otherwise.
     */
    public static function isWindows() {
        return (substr(PHP_OS, 0, 3) === 'WIN');
    }

}
