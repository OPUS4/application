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
 * @package     Application_Configuration
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2017
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * Class for handling file type configuration.
 *
 * TODO is a class FileType necessary for access to information about a specific type?
 *      access getFileType()->getContentDisposition OR getContentDisposition($fileType)
 * TODO different configuration structure might be more efficient
 */
class Application_Controller_Action_Helper_FileTypes extends Application_Controller_Action_Helper_Abstract
{

    private $_validMimeTypes = null;

    /**
     * Returns valid MIME-Types for imports.
     *
     * TODO not used by "publish"-module yet
     *
     * @return mixed
     */
    public function getValidMimeTypes() {
        if (is_null($this->_validMimeTypes))
        {

            $config = $this->getConfig();

            $mimeTypes = array();

            $fileTypes = $config->filetypes->toArray();

            foreach ($fileTypes as $extension => $fileType)
            {
                if (isset($fileType['mimeType']) && $extension !== 'default')
                {
                    $mimeTypes[$extension] = $fileType['mimeType'];
                }
            }

            $this->_validMimeTypes = $mimeTypes;
        }

        return $this->_validMimeTypes;
    }

    /**
     * Returns content disposition for MIME-type used for downloads.
     */
    public function getContentDisposition($mimeType = null)
    {
        $config = $this->getConfig();

        $fileTypes = $config->filetypes;

        $contentDisposition = $fileTypes->default->contentDisposition;

        foreach($fileTypes->toArray() as $extension => $fileType)
        {
            if (isset($fileType['mimeType']) && $fileType['mimeType'] === $mimeType)
            {
                if (isset($fileType['contentDisposition']))
                {
                    $contentDisposition = $fileType['contentDisposition'];
                    break;
                }

            }
        }

        return $contentDisposition;
    }

}
