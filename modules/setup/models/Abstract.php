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
 * @package     Module_Setup
 * @author      Edouard Simon (edouard.simon@zib.de)
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Base class for setup models
 */
abstract class Setup_Model_Abstract {

    /**
     * Source Paths for translation resources
     */
    protected $_tmxSources = array();

    /**
     * Target file for writing translation resources
     */
    protected $_tmxTarget;

    /**
     * language resources
     */
    protected $_tmxContents;

    /**
     * Array of with keys containing source paths for data resources
     * and values containing respective content
     */
    protected $_contentSources = array();

    /**
     * Zend_Log, can be set via @see setLog()
     * or will be set from Zend:Registry as needed
     */
    protected $_log;

    /**
     * @param Zend_Config|array $config Object or Array containing configuration
     *                                  parameters (@see setConfig() for details).
     * @param Zend_Log $log             Instance of Zend_Log (@see setLog())
     */
    public function __construct($config = null, $log = null) {
        if (!is_null($config)) {
            $this->setConfig($config);
        }
        if (!is_null($log)) {
            $this->setLog($log);
        }
    }

    /**
     * Set configuration parameters. The parameter keys must
     * match corresponding method names as follows:
     * E.g. the key "basePath" will match method "setBasePath($value)"
     */
    public function setConfig($config) {
        if ($config instanceOf Zend_Config) {
            $config = $config->toArray();
        }
        foreach ($config as $key => $value) {
            $methodName = 'set' . ucfirst($key);
            if (method_exists($this, $methodName)) {
                $this->$methodName($value);
            }
            else {
                throw new Setup_Model_Exception("Invalid configuration key '$key'. No corresponding method found.");
            }
        }
    }

    /**
     * Dump model content to array.
     * The output structure should match the input expected by @see fromArray().
     *
     * @result array|false returns array on success, false on failure.
     */
    abstract public function toArray();

    /**
     * Set model content from array.
     * Expects input matching the structure
     * of the result returned by @see toArray().
     * @param array $array Array of model content.
     */
    abstract public function fromArray(array $array);

    /**
     * @param array $tmxSourcePaths Array of file paths used for reading tmx content
     *
     */
    public function setTranslationSources(array $tmxSourcePaths) {
        $this->_tmxSources = $tmxSourcePaths;
    }

    /**
     * @param string $tmxPath file path used for writing tmx content.
     */
    public function setTranslationTarget($tmxTargetPath) {
        $this->verifyWriteAccess($tmxTargetPath);
        $this->_tmxTarget = $tmxTargetPath;
    }

    /**
     * @param array $contentFiles  Array of file paths used for
     *                          reading and writing content.
     * @throws Setup_Model_FileNotReadableException
     * @throws Setup_Model_FileNotWriteableException
     */
    public function setContentSources(array $contentFiles) {
        foreach ($contentFiles as $filename) {
            $this->addContentSource($filename);
        }
    }

    /**
     * @param string $filename  full path of file used for
     *                          reading and writing content.
     * @throws Setup_Model_FileNotReadableException
     * @throws Setup_Model_FileNotWriteableException
     */
    public function addContentSource($filename) {
        $filePath = realpath($filename);
        if ($filePath == false) {
            throw new Setup_Model_FileNotFoundException($filename);
        }
        $this->verifyReadAccess($filePath);
        $this->verifyWriteAccess($filePath);
        if (!isset($this->_contentSources[$filePath])) {
            $this->_contentSources[$filePath] = null;
        }
    }

    /**
     * @param string $filename      file path for content.
     *                              If no file name is set,
     *                              all content is returned in an array.
     * @throws Setup_Model_FileNotReadableException
     * @return array|string Content found in file refered to by key.
     */
    public function getContent($filename = null) {
        $result = array();
        if (is_null($filename)) {
            foreach ($this->_contentSources as $filePath) {
                $this->verifyReadAccess($filePath);
                $result[$filePath] = file_get_contents($filePath);
            }
        }
        else {
            $filePath = realpath($filename);
            if (!array_key_exists($filePath, $this->_contentSources)) {
                throw new Setup_Model_Exception("$filePath is not a valid source file.");
            }
            $result = file_get_contents($filePath);
        }
        return $result;
    }

    /**
     * @param array $content   Array of key value pairs with values containing
     *                      content to be stored in file refered to by keys.
     * @throws Setup_Model_FileNotReadableException
     * @throws Setup_Model_FileNotWriteableException
     */
    public function setContent(array $content) {
        foreach ($content as $filename => $contents) {
            if (!isset($this->_contentSources[$filename])) {
                $this->addContentSource($filename);
                $this->_contentSources[$filename] = $contents;
            }
        }
    }

    /**
     * @return array|bool   Returns array of translation units from the first
     *                      file that is found in translation source paths
     *                      (@see setTranslationSourcePaths).
     *                      Returns false if no valid file path can be found.
     */
    public function getTranslation() {
        $tmxFile = new Application_Util_TmxFile();
        foreach ($this->_tmxSources as $source) {
            if (is_file($source) && is_readable($source)) {
                $tmxFile->load($source);
            }
        }
        $result = $tmxFile->toArray();
        return empty($result) ? false : $result;
    }

    /**
     * Computes the difference between the stored translation
     * and the array provided. Translation units are returned as a whole,
     * i.e. if only one variant is altered, the other variant(s) will be returned
     * as well.
     *
     * @param array $translation Array containing altered translations
     * @return array Array containing altered or added translation units
     */
    public function getTranslationDiff(array $array) {
        // an anonymous function that computes the difference between two
        // arrays recursively.
        // (taken from http://www.php.net/manual/en/function.array-diff.php#91756)
        $recursiveDiff = function($arrayOne, $arrayTwo) use (&$recursiveDiff) {
                    $aReturn = array();
                    foreach ($arrayOne as $mKey => $mValue) {
                        if (array_key_exists($mKey, $arrayTwo)) {
                            if (is_array($mValue)) {
                                $aRecursiveDiff = $recursiveDiff($mValue, $arrayTwo[$mKey]);
                                if (count($aRecursiveDiff)) {
                                    $aReturn[$mKey] = $aRecursiveDiff;
                                }
                            }
                            else {
                                if ($mValue != $arrayTwo[$mKey]) {
                                    $aReturn[$mKey] = $mValue;
                                }
                            }
                        }
                        else {
                            $aReturn[$mKey] = $mValue;
                        }
                    }
                    return $aReturn;
        };
        $currentTranslation = $this->getTranslation();
        if (empty($currentTranslation)) {
            $result = $array;
        }
        else {
            $diffArray = $recursiveDiff($array, $currentTranslation);
            $result = array_replace_recursive(array_intersect_key($currentTranslation, $diffArray), $diffArray);
        }
        return $result;
    }

    /**
     * Set translation content to be stored (@see store)
     * @param array $tmxContent Content to be stored in tmx target file.
     * @param bool $diff If true, only difference to saved data is set.
     */
    public function setTranslation(array $array, $diff = true) {
        if ($diff) {
            $array = $this->getTranslationDiff($array);
        }
        $this->_tmxContents = $array;
    }

    /**
     * Store all tmx and content that has been set.
     * This is performed as a transaction, reverting
     * changes already written if one write fails.
     *
     * @return bool Returns true on success, false on failure.
     */
    public function store() {
        $result = true;

        $tmxBackup = $savedContent = array();

        try {
            if (!empty($this->_tmxContents)) {
                $this->verifyWriteAccess($this->_tmxTarget);
                $tmxFile = new Application_Util_TmxFile();
                if (is_file($this->_tmxTarget)) {
                    $tmxFile->load($this->_tmxTarget);
                    // backup in case a write operation fails
                    $tmxBackup = $tmxFile->toArray();
                }

                $tmxFile->fromArray($this->_tmxContents);
                $result = $tmxFile->save($this->_tmxTarget);
                if (!$result) {
                    throw new Setup_Model_Exception("Saving File '{$this->_tmxTarget}' failed");
                }
            }

            foreach ($this->_contentSources as $filename => $contents) {
                if (!is_null($contents)) {
                    $this->verifyWriteAccess($filename);
// backup stored content to restore in case a write operation fails
                    $savedContent[$filename] = file_get_contents($filename);
                    $result = (false !== file_put_contents($filename, $contents)) && $result;
                    if (!$result) {
                        throw new Setup_Model_Exception("Saving File '$filename' failed");
                    }
                }
            }
        } catch (Setup_Model_Exception $se) {
            if (!empty($tmxBackup)) {
                $tmxFile = new Application_Util_TmxFile();
                $tmxFile->fromArray($tmxBackup);
                $tmxFile->save($this->_tmxTarget);
            }

            if (!empty($savedContent)) {
                foreach ($savedContent as $filename => $contents) {
                    file_put_contents($filename, $contents);
                }
            }
            $this->log($se->getMessage());
            $result = false;
        }

        return $result;
    }

    /**
     * Check if a file exists and is readable
     *
     * @param string $file Path to file
     *
     * @throws Setup_Model_FileNotReadableException
     */
    public function verifyReadAccess($file) {
        if (!(is_file($file) && is_readable($file))) {
            throw new Setup_Model_FileNotReadableException($file);
        }
    }

    /**
     * Check if a file is writeable.
     * If the file does not exists, write access is checked for the parent directory.
     *
     * @param string $file Path to file
     *
     * @throws Setup_Model_FileNotWriteableException
     */
    public function verifyWriteAccess($file) {
        if (
                (!( is_file($file) && is_writable($file) ))
                && (!( is_dir(dirname($file)) && is_writeable(dirname($file))) )
        ) {
            throw new Setup_Model_FileNotWriteableException($file);
        }
    }

    /**
     * Set instance of Zend_Log if necessary
     * Will otherwise be set from Zend_Registry in @see log() as needed
     */
    public function setLog(Zend_Log $log) {
        $this->_log = $log;
    }

    protected function log($message, $priority = Zend_Log::ERR) {
        if (is_null($this->_log)) {
            $this->setLog(Zend_Registry::get('Zend_Log'));
        }
        $this->_log->log($message, $priority);
    }

}
