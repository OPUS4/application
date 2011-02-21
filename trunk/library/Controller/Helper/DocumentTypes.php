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
 * @category    TODO
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
     * @var <type>
     */
    private $config;

    /**
     * Variable to store document types for additional calls.
     * @var array($docTypeName => $docTypeName)
     */
    private $docTypes;

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
        else {
            $allDocTypes = $this->_getDocTypeFileNames();

            $include = $this->_getIncludeList();

            // include all or only listed document types
            if (!empty($include)) {
                $docTypes = array();

                foreach ($include as $docType) {
                    if (array_search($docType, $allDocTypes)) {
                        $docTypes[$docType] = $docType;
                    }
                }
            }
            else {
                $docTypes = $allDocTypes;
            }

            $exclude = $this->_getExcludeList();

            // remove all listed document types
            if (!empty($exclude)) {
                foreach ($exclude as $docType) {
                    unset($docTypes[$docType]);
                }
            }

            $this->docTypes = $docTypes;

            return $docTypes;
        }
    }

    /**
     * Checks if a document type is supported.
     *
     * @param string $documentType
     * @return boolean
     */
    public function isValid($documentType) {
        $docTypes = $this->getDocumentTypes();

        return array_key_exists($documentType, $docTypes);
    }

    /**
     * Returm DOMDocument for document type.
     *
     * @param string $documentType
     * @return DOMDocument 
     * 
     * TODO catch parsing errors
     */
    public function getDocument($documentType) {
        if (!$this->isValid($documentType)) {
            return null; // TODO throw exception
        }

        $xmlFile = $this->getDocTypesPath() . DIRECTORY_SEPARATOR . $documentType . ".xml";

        $dom = new DOMDocument();

        $dom->load($xmlFile);

        return $dom;
    }

    /**
     * Returns name of template for document type.
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
            throw new Exception('Path to document types not configured.');
        }
        
        return $path;
    }

    /**
     * Returns filenames of XML files in document types path.
     *
     * @return array
     *
     * TODO try catch around IO operations?
     */
    protected function _getDocTypeFileNames() {
        $docTypesPath = $this::getDocTypesPath();

        $files = array();

        $dirHandle = opendir($docTypesPath);

        if ($dirHandle) {
            while (($file = readdir($dirHandle)) !== false) {
                // ignore non-Xml files
                if (preg_match("/.xml$/", $file) === 0) {
                    continue;
                }

                $path_parts = pathinfo($file);

                $filename = $path_parts['filename'];
                $basename = $path_parts['basename'];
                $extension = $path_parts['extension'];

                if (($basename !== '.') and ($basename !== '..') and ($extension === 'xml')) {
                    $files[$filename] = $filename;
                }
            }
            closedir($dirHandle);
        }
        else {
            // TODO throw something
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
            return null;
        }

        $include = $this->config->documentTypes->include;

        $result = null;

        if (!empty($include)) {
            $result = explode(",", $include);
            Util_Array::trim($result);
        }

        return $result;
    }

    /**
     * Returns array with names of exluded document types.
     * @return array of strings
     */
    protected function _getExcludeList() {
        if (!isset($this->config->documentTypes->exclude)) {
            return null;
        }

        $exclude = $this->config->documentTypes->exclude;

        $result = null;

        if (!empty($exclude)) {
            $result = explode(",", $exclude);
            Util_Array::trim($result);
        }

        return $result;
    }

}

?>
