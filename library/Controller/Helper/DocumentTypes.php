<?php
/*
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
 * @package     Controller
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Helper class for getting document types and template names.
 *
 */
class Controller_Helper_DocumentTypes extends Zend_Controller_Action_Helper_Abstract {

    /**
     * Configuration.
     *
     * @var Zend_Config
     */
    private $config;

    /**
     * Variable to store document types for additional calls.
     * @var array($docTypeName => $docTypeName)
     */
    private $docTypes;

    /**
     * Variable to store errors of document-type validation
     * @var array ($documentType => $errorMessage)
     */
    private $errors;

    /**
     * Constructs instances.
     */
    public function __construct() {
        $this->config = Zend_Registry::get('Zend_Config');
    }

    /**
     * Returns filtered list of document types.
     * @return array
     */
    public function getDocumentTypes() {
        if (isset($this->docTypes)) {
            return $this->docTypes;
        }
        
        $allDocTypes = $this->_getDocTypeFileNames();
        $docTypes = $allDocTypes;

        $include = $this->_getIncludeList();

        // include only listed document types
        if (!empty($include)) {
            $docTypes = array();

            foreach ($include as $docType) {
                if (array_search($docType, $allDocTypes)) {
                    $docTypes[$docType] = $docType;
                }
            }
        }

        // remove all listed document types
        foreach ($this->_getExcludeList() as $docType) {
            unset($docTypes[$docType]);
        }

        $this->docTypes = $docTypes;
        return $docTypes;
    }

    /**
     * Checks if a document type is supported.
     *
     * @param string $documentType
     * @return boolean
     */
    public function isValid($documentType) {
        return array_key_exists($documentType, $this->getDocumentTypes());
    }

    /**
     * Returns DOMDocument for document type.
     *
     * @param string $documentType
     * @return DOMDocument
     * @throws Application_Exception if invalid documentType passed.
     */
    public function getDocument($documentType) {
        if (!$this->isValid($documentType)) {
            throw new Application_Exception('Unable to load invalid document type "' . $documentType . '"');
        }

        $dom = new DOMDocument();
        $dom->load($this->getDocTypesPath() . DIRECTORY_SEPARATOR . $documentType . '.xml');

        // clear libxml error buffer and enable user error handling
        libxml_clear_errors();
        libxml_use_internal_errors(true);

        if (!$dom->schemaValidate(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'Opus' .
                DIRECTORY_SEPARATOR . 'Document' . DIRECTORY_SEPARATOR . 'documenttype.xsd')) {
            libxml_clear_errors();
            throw new Application_Exception('given xml document type definition for document type ' . $documentType .
                ' is not valid');
        }

        return $dom;
    }

    /**
     * Returns name of PHML template file for document type.
     * This method does NOT check if the corresponding PHTML file exist or is readable.
     *
     * @param string $documentType
     * @return
     */
    public function getTemplateName($documentType) {
        if (!$this->isValid($documentType)) {
            return null; // TODO throw exception
        }

        $template = null;

        if (isset($this->config->documentTypes->templates->$documentType)) {
            $template = $this->config->documentTypes->templates->$documentType;
        }

        if (!empty($template)) {
            return $template;
        }
        else {
            return $documentType;
        }
    }

    /**
     * Returns path for document types.
     *
     * @return string
     */
    public function getDocTypesPath() {
        $path = null;

        if (isset($this->config->publish->path->documenttypes)) {
            $path = $this->config->publish->path->documenttypes;
        }

        if (empty($path)) {
            throw new Application_Exception('Path to document types not configured.');
        }

        return $path;
    }

    /**
     * Returns filenames of XML files in document types path.
     *
     * @return array
     *
     */
    protected function _getDocTypeFileNames() {
        $docTypesPath = $this::getDocTypesPath();

        if (!is_dir($docTypesPath) || !is_readable($docTypesPath)) {
            throw new Application_Exception('could not read document type definitions');
        }

        $files = array();
        foreach (new DirectoryIterator($docTypesPath) as $fileinfo) {
            if ($fileinfo->isFile()) {
                if (strrchr($fileinfo->getBaseName(), '.') == '.xml') {
                    $filename = $fileinfo->getBaseName('.xml');
                    $files[$filename] = $filename;                    
                }
            }
        }
        asort($files);
        return $files;
    }

    /**
     * Gets called when the helper is used like a method of the broker.
     *
     * @return array
     */
    public function direct() {
        return $this->getDocumentTypes();
    }

    /**
     * Returns array with names of included document types.
     * @return array of strings
     */
    protected function _getIncludeList() {
        if (!isset($this->config->documentTypes->include)) {
            return array();
        }
        return $this->_getList($this->config->documentTypes->include);
    }

    /**
     * Returns array with names of exluded document types.
     * @return array of strings
     */
    protected function _getExcludeList() {
        if (!isset($this->config->documentTypes->exclude)) {
            return array();
        }
        return $this->_getList($this->config->documentTypes->exclude);
    }

    private function _getList($str) {
        $result = explode(',', $str);
        Util_Array::trim($result);
        return $result;
    }

    /*
     * validates all document types in folder getDocTypesPath()
     * returns an array ($filename => bool)
     */
    public function validateDocuments() {
        $documents = array();
        if ($handle = opendir($this::getDocTypesPath())) {
            while(false !== ($file = readdir($handle))) {
                $fileInfo = explode('.', $file);
                if (strlen($file) >= 4 && $fileInfo[1] == 'xml') {
                    $documents[$fileInfo[0]] = $this->validate($fileInfo[0]);
                }
            }
        }
        return $documents;
    }

    /*
     * validates a single file
     * writes errors into array $this->errors ($filename, libXMLError)
     * returns bool
     */
    public function validate($filename) {
        if (is_null($this->errors)) {
            $this->errors = array();
        }
        $domDoc = new DOMDocument();
        $domDoc->load($this::getDocTypesPath() . '/' . $filename . '.xml');
        $isValid = 0;
        libxml_clear_errors();
        libxml_use_internal_errors(true);
        try {
            $isValid = $domDoc->schemaValidate($this->config->documentTypes->xmlSchema);
            $this->errors[$filename] = libxml_get_errors();
        }
        catch (Exception $e) {
            $this->errors[$filename] = $e->getMessage();
            return 0;
        }
        return $isValid;
    }

    public function getErrors () {
        return $this->errors;
    }

}

