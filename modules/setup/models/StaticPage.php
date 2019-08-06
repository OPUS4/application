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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 *
 */
class Setup_Model_StaticPage extends Setup_Model_Abstract
{

    /**
     * name of the page to edit
     */
    protected $_pageName;

    /**
     *  base path for content files
     */
    protected $_contentBasepath;

    /**
     * decide wether to use external content file (as in contact, imprint)
     * or save content data in tmx file (as in home page)
     */
    protected $_useContentFile = true;

    /**
     * @param string $pageName          Name of the page
     * @param Zend_Config|array $config Object or Array containing configuration
     *                                  parameters (@see setConfig() for details).
     * @param Zend_Log $log             Instance of Zend_Log (@see setLog())
     */
    public function __construct($pageName, $config = null, $log = null)
    {
        $this->_pageName = $pageName;
        parent::__construct($config, $log);
    }

    public function setPageNames($pageNames)
    {
        // check if pageName is valid
        if (! in_array($this->_pageName, $pageNames)) {
            throw new Setup_Model_Exception('Invalid page name, not found in configuration. ');
        }
    }

    /**
     * Set path to directory where the translation target file resides.
     *  The complete path to the file is generated from the page name.
     *
     * @param string $tmxTargetPath directory path used to write tmx content
     */
    public function setTranslationTargetPath($tmxTargetPath)
    {
        $this->setTranslationTarget($tmxTargetPath . DIRECTORY_SEPARATOR . $this->_pageName . '.tmx');
    }

    /**
     * Set usage of external content file. If true, content data is saved to
     * external file (@see $useContentFile).
     *
     * @param string $tmxTargetPath directory path used to write tmx content
     */
    public function setUseContentFile($bool = true)
    {
        $this->_useContentFile = $bool;
    }

    /**
     * Set paths to directories where the translation source files resides.
     * The complete path to the files is generated from the page name,
     * i.e. each file must be named after the page name.
     * @param array $tmxSourcePaths Array of directory paths used for reading tmx content
     *
     */
    public function setTranslationSourcePaths(array $tmxSourcePaths)
    {
        $filePaths = [];
        foreach ($tmxSourcePaths as $tmxsrc) {
            $filePaths[] = $tmxsrc . DIRECTORY_SEPARATOR . $this->_pageName . '.tmx';
        }
        parent::setTranslationSources($filePaths);
    }

    /**
     * Path to directory containing content file.
     * The file must be named after the page name.
     *
     * @param string $basePath name of directory used to read / write page content
     */
    public function setContentBasepath($basePath)
    {
        $this->_contentBasepath = $basePath;
    }

    /**
     * @see Description in abstract base class
     */
    public function toArray()
    {
        $languages = ['de', 'en']; // TODO get languages from configuration - maybe more in the future

        $resultArray = [];
        $translationUnits = $this->getTranslation();
        foreach ($languages as $language) {
            $resultArray[$language] = [];
            if ($this->_useContentFile) {
                $fileName = "{$this->_pageName}.$language.txt";
                $filePath = $this->_contentBasepath . DIRECTORY_SEPARATOR . $fileName;
                $this->addContentSource($filePath);
                $resultArray[$language]['file']['filename'] = $fileName;
                $resultArray[$language]['file']['contents'] = $this->getContent($filePath);
            }
            if (! empty($translationUnits)) {
                foreach ($translationUnits as $translationUnit => $variants) {
                    $resultArray[$language]['key'][$translationUnit] = $variants[$language];
                }
            }
        }

        return $resultArray;
    }

    /**
     * @see Description in abstract base class
     */
    public function fromArray(array $data)
    {
        $resultData = [];
        $resultTmx = new Application_Translate_TmxFile();

        foreach ($data as $language => $fields) {
            foreach ($fields as $key => $val) {
                switch ($key) {
                    case 'file':
                        if (! is_array($val)
                                || ! isset($val['filename'])
                                || ! isset($val['contents'])
                        ) {
                            throw new Setup_Model_Exception('Invalid data structure');
                        }
                        $filePath = $this->_contentBasepath . DIRECTORY_SEPARATOR . $val['filename'];
                        $resultData[$filePath] = $val['contents'];
                        break;
                    case 'key':
                        foreach ($val as $translationUnit => $variant) {
                            $resultTmx->setTranslation($translationUnit, $language, $variant);
                        }
                        break;
                    default:
                        throw new Setup_Model_Exception('Failed loading array. Invalid data structure.');
                }
            }
        }
        $this->setContent($resultData);
        $this->setTranslation($resultTmx->toArray());
    }
}
