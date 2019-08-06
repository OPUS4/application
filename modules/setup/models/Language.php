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
 *
 */
class Setup_Model_Language extends Setup_Model_Abstract
{

    protected $_moduleBasepath;
    protected $_moduleName;
    protected $_languageDir;
    protected $_filename;

    const CUSTOM_DIR = "language_custom";

    /**
     * Set parameters needed to assemble translation source file.
     * The array must include the following fields:
     * moduleBasepath
     * moduleName
     * languageDirectory
     * filename
     * @param array $params Array including the above fields
     */
    public function setTranslationSourceParams(array $params)
    {
        if (! isset($params['moduleBasepath'])
                || ! isset($params['moduleName'])
                || ! isset($params['languageDirectory'])
                || ! isset($params['filename'])) {
            throw new Setup_Model_Exception('Invalid configuration');
        }

        $this->_moduleBasepath = $params['moduleBasepath'];
        $this->_moduleName = $params['moduleName'];
        $this->_languageDir = $params['languageDirectory'];
        $this->_filename = $params['filename'];
    }

    public function fromArray(array $array)
    {
        if (empty($this->_moduleBasepath)
                || empty($this->_moduleName)
                || empty($this->_languageDir)
                || empty($this->_filename)) {
            throw new Setup_Model_Exception('Invalid configuration');
        }
        // when editing non-writeable file (i.e. module/language/xyz.tmx)
        // make sure custom translations are not overwritten
        $this->setTranslationSources(
            ["{$this->_moduleBasepath}/{$this->_moduleName}/" . self::CUSTOM_DIR . "/{$this->_filename}"]
        );

        $this->setTranslation($array);
    }

    public function toArray()
    {
        if (empty($this->_moduleBasepath)
                || empty($this->_moduleName)
                || empty($this->_languageDir)
                || empty($this->_filename)) {
            throw new Setup_Model_Exception('Invalid configuration');
        }
        $this->setTranslationSources(
            ["{$this->_moduleBasepath}/{$this->_moduleName}/{$this->_languageDir}/{$this->_filename}"]
        );

        return $this->getTranslation();
    }
}
