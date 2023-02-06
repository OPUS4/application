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

use Opus\Common\Config;
use Opus\Document;

/**
 * Helper class for getting document types and template names.
 */
class Application_Controller_Action_Helper_DocumentTypes extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Names and paths for document type definition files.
     *
     * @var array
     */
    private $allDocTypes;

    /**
     * Array with names and paths for template files.
     *
     * @var array
     */
    private $templates;

    /**
     * Variable to store document types for additional calls.
     *
     * @var array ($docTypeName => $docTypeName)
     */
    private $docTypes;

    /**
     * Variable to store errors of document-type validation
     *
     * @var array ($documentType => $errorMessage)
     */
    private $errors;

    /**
     * Returns filtered list of document types.
     *
     * @return array
     */
    public function getDocumentTypes()
    {
        if (! isset($this->docTypes)) {
            $allDocTypes = $this->getAllDocumentTypes();

            $docTypes = $allDocTypes;

            $include = $this->getIncludeList();

            // include only listed document types
            if (! empty($include)) {
                $docTypes = [];

                foreach ($include as $docType) {
                    if (array_key_exists($docType, $allDocTypes)) {
                        $docTypes[$docType] = $allDocTypes[$docType];
                    }
                }
            }

            // remove all listed document types
            foreach ($this->getExcludeList() as $docType) {
                unset($docTypes[$docType]);
            }

            $this->docTypes = $docTypes;
        }

        return $this->docTypes;
    }

    /**
     * Returns array with names and paths for all document types.
     *
     * @return array
     */
    public function getAllDocumentTypes()
    {
        if (! isset($this->allDocTypes)) {
            $this->allDocTypes = $this->getDocTypeFileNames();
        }

        return $this->allDocTypes;
    }

    /**
     * Checks if a document type is supported.
     *
     * @param string $documentType
     * @return bool
     */
    public function isValid($documentType)
    {
        return array_key_exists($documentType, $this->getDocumentTypes());
    }

    /**
     * Returns DOMDocument for document type.
     *
     * @param string $documentType
     * @return DOMDocument
     * @throws Application_Exception If invalid documentType passed.
     */
    public function getDocument($documentType)
    {
        if (! $this->isValid($documentType)) {
            throw new Application_Exception('Unable to load invalid document type "' . $documentType . '"');
        }

        $dom = new DOMDocument();
        $dom->load($this->getPathForDocumentType($documentType));

        // clear libxml error buffer and enable user error handling
        libxml_clear_errors();
        $useInternalErrors = libxml_use_internal_errors(true);

        if (! $dom->schemaValidate($this->getXmlSchemaPath())) {
            libxml_clear_errors();
            throw new Application_Exception(
                'given xml document type definition for document type ' . $documentType
                . ' is not valid'
            );
        }
        libxml_use_internal_errors($useInternalErrors);
        libxml_clear_errors();
        return $dom;
    }

    /**
     * Returns name of PHTML template file for document type.
     * This method does NOT check if the corresponding PHTML file exist or is readable.
     *
     * @param string $documentType
     * @return string|null
     */
    public function getTemplateName($documentType)
    {
        if (! $this->isValid($documentType)) {
            return null; // TODO throw exception
        }

        $template = null;

        $config = $this->getConfig();

        if (isset($config->documentTypes->templates->$documentType)) {
            $template = $config->documentTypes->templates->$documentType;
        }

        if (! empty($template)) {
            return $template;
        } else {
            return $documentType;
        }
    }

    /**
     * Returns array with template names and paths.
     *
     * @return array
     */
    public function getTemplates()
    {
        $config = $this->getConfig();

        if (! isset($this->templates)) {
            if (! isset($config->publish->path->documenttemplates)) {
                throw new Application_Exception('invalid configuration: publish.path.documenttemplates is not defined');
            }

            $path = $config->publish->path->documenttemplates;

            if ($path instanceof Zend_Config) {
                $path = $path->toArray();
            }

            $iterator = $this->getDirectoryIterator($path);

            $files = [];

            foreach ($iterator as $fileinfo) {
                if ($fileinfo->isFile()) {
                    if (strrchr($fileinfo->getBaseName(), '.') === '.phtml') {
                        $filename         = $fileinfo->getBaseName('.phtml');
                        $files[$filename] = $fileinfo->getPathname();
                    }
                }
            }

            $this->templates = $files;
        }

        return $this->templates;
    }

    /**
     * Returns path to file for template name.
     *
     * @param string $templateName Name of template
     * @return null|string Path to template file
     * @throws Application_Exception
     */
    public function getTemplatePath($templateName)
    {
        $templates = $this->getTemplates();

        if (isset($templates[$templateName])) {
            return $templates[$templateName];
        } else {
            return null;
        }
    }

    /**
     * Returns path for document types.
     *
     * @return string
     * @throws Application_Exception
     */
    public function getDocTypesPath()
    {
        $path = null;

        $config = $this->getConfig();

        if (isset($config->publish->path->documenttypes)) {
            $path = $config->publish->path->documenttypes;
        }

        if (empty($path)) {
            throw new Application_Exception('Path to document types not configured.');
        }

        if ($path instanceof Zend_Config) {
            return $path->toArray();
        } else {
            return $path;
        }
    }

    /**
     * Returns filenames of XML files in document types path.
     *
     * @return array
     */
    protected function getDocTypeFileNames()
    {
        $docTypesPath = $this::getDocTypesPath();

        $iterator = $this->getDirectoryIterator($docTypesPath);

        $files = [];

        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile()) {
                if (strrchr($fileinfo->getBaseName(), '.') === '.xml') {
                    $filename         = $fileinfo->getBaseName('.xml');
                    $files[$filename] = $fileinfo->getPathname();
                }
            }
        }

        ksort($files);

        return $files;
    }

    /**
     * Returns iterator for one or more directories.
     *
     * @param string|array $docTypesPath Path(s)
     * @return AppendIterator|DirectoryIterator|null
     * @throws Application_Exception
     */
    public function getDirectoryIterator($docTypesPath)
    {
        $iterator = null;

        if (is_array($docTypesPath)) {
            $iterator = new AppendIterator();

            foreach ($docTypesPath as $path) {
                if (! is_dir($path) || ! is_readable($path)) {
                    throw new Application_Exception('could not read document type definitions');
                }

                $iterator->append(new DirectoryIterator($path));
            }
        } else {
            if (! is_dir($docTypesPath) || ! is_readable($docTypesPath)) {
                throw new Application_Exception('could not read document type definitions');
            }
            $iterator = new DirectoryIterator($docTypesPath);
        }

        return $iterator;
    }

    /**
     * Gets called when the helper is used like a method of the broker.
     *
     * @return array
     */
    public function direct()
    {
        return $this->getDocumentTypes();
    }

    /**
     * Returns array with names of included document types.
     *
     * @return array of strings
     */
    protected function getIncludeList()
    {
        $config = $this->getConfig();

        if (! isset($config->documentTypes->include)) {
            return [];
        }
        return $this->getList($config->documentTypes->include);
    }

    /**
     * Returns array with names of exluded document types.
     *
     * @return array of strings
     */
    protected function getExcludeList()
    {
        $config = $this->getConfig();

        if (! isset($config->documentTypes->exclude)) {
            return [];
        }
        return $this->getList($config->documentTypes->exclude);
    }

    /**
     * @param string $str
     * @return false|string[]
     */
    private function getList($str)
    {
        $result = explode(',', $str);
        Application_Util_Array::trim($result);
        return $result;
    }

    /**
     * Validates all document types in folder getDocTypesPath().
     * returns an array ($filename => bool)
     *
     * @return array
     */
    public function validateAll()
    {
        $documents = [];
        $iterator  = $this->getDirectoryIterator($this->getDocTypesPath());

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                if (strrchr($fileInfo->getBaseName(), '.') === '.xml') {
                    $filename             = $fileInfo->getBaseName('.xml');
                    $documents[$filename] = $this->validate($filename);
                }
            }
        }

        return $documents;
    }

    /**
     * validates a single file
     * writes errors into array $this->errors ($filename, libXMLError)
     * returns bool
     *
     * @param string $documentType
     * @return bool
     */
    public function validate($documentType)
    {
        if ($this->errors === null) {
            $this->errors = [];
        }
        $domDoc = new DOMDocument();
        $domDoc->load($this->getPathForDocumentType($documentType));

        libxml_clear_errors();
        libxml_use_internal_errors(true);

        try {
            $isValid                     = $domDoc->schemaValidate($this->getXmlSchemaPath());
            $this->errors[$documentType] = libxml_get_errors();
        } catch (Exception $e) {
            $this->errors[$documentType] = $e->getMessage();
            return false;
        }
        libxml_use_internal_errors(false);
        libxml_clear_errors();
        return $isValid;
    }

    /**
     * Returns errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Returns path to xml schema for validation of document type definitions.
     *
     * @return string
     */
    public function getXmlSchemaPath()
    {
        $reflector = new ReflectionClass(Document::class);
        return dirname($reflector->getFileName()) . DIRECTORY_SEPARATOR . 'Document' . DIRECTORY_SEPARATOR
            . 'documenttype.xsd';
    }

    /**
     * Returns the actual path for a document type definition file.
     *
     * @param string $name Name of document type
     * @return string|null Path to document type definition file
     */
    public function getPathForDocumentType($name)
    {
        $docTypes = $this->getAllDocumentTypes();

        if (isset($name) && ! empty($name)) {
            return $docTypes[$name];
        }

        return null;
    }

    /**
     * @return Zend_Config
     */
    public function getConfig()
    {
        return Config::get();
    }
}
