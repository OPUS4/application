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
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
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
    protected $tmxSources = array();

    /**
     * Target file for writing translation resources
     */
    protected $tmxTarget;

    /**
     * language resources
     */
    protected $tmxContents;

    /**
     * Source paths for data resources
     */
    protected $dataSources = array();

    /**
     * data resources
     */
    protected $dataContents = array();

    /**
     * Zend_Log, can be set via @see setLog()
     * or will be set from Zend:Registry as needed
     */
    protected $log;

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
            } else {
                throw new Setup_Model_Exception("Invalid configuration key '$key'. No corresponding method found.");
            }
        }
    }

    /**
     * Dump model data to array. 
     * The output structure should match the input expected by @see fromArray().
     * 
     * @result array|false returns array on success, false on failure.
     */
    
    abstract public function toArray();
    
    /**
     * Set model data from array.
     * Expects input matching the structure 
     * of the result returned by @see toArray().
     * @param array $array Array of model data.
     */
    abstract public function fromArray(array $array);
    
    /**
     * @param array $tmxSourcePaths Array of file paths used for reading tmx data
     * 
     */
    public function setTranslationSources(array $tmxSourcePaths) {
        $this->tmxSources = $tmxSourcePaths;
    }

    /**
     * @param string $tmxPath file path used for writing tmx data.
     */
    public function setTranslationTarget($tmxTargetPath) {
        $this->verifyWriteAccess($tmxTargetPath);
        $this->tmxTarget = $tmxTargetPath;
    }

    /**
     * @param array $dataFiles  Array of file paths used for 
     *                          reading and writing content data.
     * @throws Setup_Model_FileNotReadableException
     * @throws Setup_Model_FileNotWriteableException
     */
    public function setDataSources(array $dataFiles) {
        foreach ($dataFiles as $filename) {
            $this->addDataSource($filename);
        }
    }

    /**
     * @param string $filename  full path of file used for 
     *                          reading and writing content data.
     * @throws Setup_Model_FileNotReadableException
     * @throws Setup_Model_FileNotWriteableException
     */
    public function addDataSource($filename) {
        $filePath = realpath($filename);
        $this->verifyReadAccess($filePath);
        $this->verifyWriteAccess($filePath);
        if (!isset($this->dataSources[$filePath]))
            $this->dataSources[$filePath] = null;
    }

    /**
     * @param string $filename      file path for content data.
     *                              If no file name is set, 
     *                              all data is returned in an array.
     * @throws Setup_Model_FileNotReadableException
     * @return array|string Data found in file refered to by key.
     */
    public function getData($filename = null) {
        $result = array();
        if (is_null($filename)) {
            foreach ($this->dataSources as $file) {
                $this->verifyReadAccess($file);
                $result[$filePath] = file_get_contents($file);
            }
        } else {
            $filePath = realpath($filename);
            if (!array_key_exists($filePath, $this->dataSources)) {
                throw new Setup_Model_Exception("$filePath is not a valid source file.");
            } 
            $result[$filePath] = file_get_contents($filePath);
        }
        return $result;
    }

    /**
     * @param array $data   Array of key value pairs with values containing
     *                      data to be stored in file refered to by keys.
     * @throws Setup_Model_FileNotReadableException
     * @throws Setup_Model_FileNotWriteableException
     */
    public function setData(array $data) {
        foreach ($data as $filename => $contents) {
            if (!isset($this->dataSources[$filename])) {
                $this->addDataSource($filename);
                $this->dataSources[$filename] = $contents;
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
        $result = false;
        foreach ($this->tmxSources as $source) {
            if (is_file($source) && is_readable($source)) {
                $tmxFile = new Util_TmxFile($source);
                $result = $tmxFile->toArray();
                break;
            }
        }
        return $result;
    }

    /**
     * Set translation data to be stored (@see store)
     * @param array $tmxData Data to be stored in tmx target file.
     */
    public function setTranslation(array $array) {
        $this->tmxContents = $array;
    }

    /**
     * Store all tmx and content data that has been set.
     * This is performed as a transaction, reverting 
     * changes already written if one write fails.
     * 
     * @return bool Returns true on success, false on failure.
     */
    public function store() {
        $result = true;

        $savedData = array();
        try {
            if (!empty($this->tmxContents)) {
                $this->verifyWriteAccess($this->tmxTarget);
                $tmxFile = new Util_TmxFile();
                $tmxFile->fromArray($this->tmxContents);
                $result = $tmxFile->save($this->tmxTarget);
                if (!$result) {
                    throw new Setup_Exception("Saving File '{$this->tmxTarget}' failed");
                }
            }

            foreach ($this->dataSources as $filename => $contents) {
                if (!is_null($contents)) {
                    $this->verifyWriteAccess($filename);
// backup stored data to restore in case a write operation fails
                    $savedData[$filename] = file_get_contents($filename);
                    $result = (false !== file_put_contents($filename, $contents)) && $result;
                    if (!$result) {
                        throw new Setup_Exception("Saving File '$filename' failed");
                    }
                }
            }
        } catch (Setup_Exception $se) {
            if (!empty($savedData)) {
                foreach ($savedData as $filename => $contents) {
                    file_put_contents($filename, $contents);
                }
            }
            $this->log($se->getMessage());
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
        if (!(is_file($file) && is_readable($file)))
            throw new Setup_Model_FileNotReadableException($file);
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
        )
            throw new Setup_Model_FileNotWriteableException($file);
    }

    /**
     * Set instance of Zend_Log if necessary
     * Will otherwise be set from Zend_Registry in @see log() as needed
     */
    public function setLog(Zend_Log $log) {
        $this->log = $log;
    }

    protected function log($message, $priority = Zend_Log::ERR) {
        if (is_null($this->log)) {
            $this->setLog(Zend_Registry::get('Zend_Log'));
        }
        $this->log->log($priority, $message);
    }

}
