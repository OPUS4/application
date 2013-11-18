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
 * @package     Import
 * @author      Edouard Simon <edouard.simon@zib.de>
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Class to convert BibTex Data to Opus-XML using MODS-XML as intermediate format.
 * This tool relies on bib2xml (part of the bibutils suite of binaries)
 * which must be installed on the system.
 *
 */
class Admin_Model_BibtexImport {

    protected $binaryPath = 'bib2xml';
    protected $stylesheetPath;
    protected $numDocuments = 0;

    public function __construct($filename = null) {
        if (!is_null($filename)) {
            $this->setFileName($filename);
        }
        $this->stylesheetPath = APPLICATION_PATH . '/modules/admin/views/scripts/bibteximport/mods-import.xsl';
    }

    public function setFileName($filename) {
        if (!is_readable($filename)) {
            $this->log->err($filename . ' is not readable');
            throw new Admin_Model_BibtexImportException(null, Admin_Model_BibtexImportException::FILE_NOT_READABLE);
        }
        if (!mb_check_encoding(file_get_contents($filename), 'UTF-8')) {
            $this->log->err($filename . ' is not utf8-endoded');
            throw new Admin_Model_BibtexImportException(null, Admin_Model_BibtexImportException::FILE_NOT_UTF8);
        }
        $this->fileName = $filename;
    }

    public function import() {
        if (!$this->_isBinaryInstalled()) {
            $this->log->err($this->binaryPath . ' is not installed');
            throw new Admin_Model_BibtexImportException(null, Admin_Model_BibtexImportException::BINARY_NOT_INSTALLED);
        }

        $modsXml = $this->transformBibTexToModsXml($this->fileName);
        $opusXml = $this->transformModsXmlToOpusXml($modsXml);

        $this->numDocuments = DOMDocument::loadXML($opusXml);
        $this->store();
    }

    public function getNumDocuments() {
        $numDocuments = 0;
        if ($this->opusDocuments instanceOf DOMDocument) {
            $numDocuments = $this->opusDocuments->getElementsByTagName('opusDocument')->length;
        }
        return $numDocuments;
    }

    public function getDocuments() {
        $documents = null;
        if ($this->opusDocuments instanceOf DOMDocument) {
            $documents = $this->opusDocuments;
        }
        return $documents;
    }

    public function transformBibTexToModsXml($bibTexInputFile) {

        $exec_output = array();
        $exec_return = -1;
        $exec_statement = $this->binaryPath . " -i unicode --nosplit-title " . $bibTexInputFile . " 2> /dev/null";
        exec($exec_statement, $exec_output, $exec_return);
        if ($exec_return != 0) {
            throw new Admin_Model_BibtexImportException('Error calling bib2xml binary');
        }
        return implode("\n", $exec_output);
    }

    public function transformModsXmlToOpusXml($modsXml) {
        return Util_Xml::transform($modsXml, file_get_contents($this->stylesheetPath));
    }

    public function storeOpusDocuments($opusDocuments) {
        if ($opusDocuments instanceOf DOMDocument) {
            throw new Admin_Model_BibtexImportException('Expected instance of DomDocument');
        }

        $validator = new Opus_Util_MetadataImportXmlValidation($opusDocuments);
        $validator->checkValidXml();

        $config = Zend_Registry::get('Zend_Config');
        $asyncExecution = (isset($config->runjobs->asynchronous) && $config->runjobs->asynchronous);

        $table = Opus_Db_TableGateway::getInstance("Opus_Db_Jobs");
        $dbadapter = $table->getAdapter();
        $dbadapter->beginTransaction();

        try {
            foreach ($this->opusDocuments as $doc) {
                $job = new Opus_Job();
                $job->setLabel(Opus_Job_Worker_MetadataImport::LABEL);
                $job->setData(array('xml' => $doc->saveXML()));

                if ($asyncExecution) {
                    // Queue job (execute asynchronously)
                    // skip creating job if equal job already exists
                    if (true === $job->isUniqueInQueue()) {
                        $job->store();
                    }
                }
                // Execute job immediately (synchronously)
                else {
                    $import = new Opus_Job_Worker_MetadataImport($log);
                    $import->work($job);
                }
            }
        } catch (Exception $e) {
            $dbadapter->rollBack();
            throw new Admin_Model_BibtexImportException($e->getMessage(), Admin_Model_BibtexImportException::STORE_ERROR);
        }
    }

    private function _isBinaryInstalled() {
        $exec_output = array();
        $exec_return = -1;
        $exec_statement = "which " . $this->binaryPath;
        exec($exec_statement, $exec_output, $exec_return);
        return ($exec_return != 0 ? false : true);
    }

}

?>
