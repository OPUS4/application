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
 * @copyright   Copyright (c) 2017, OPUS4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * Class for handling file type configuration.
 *
 * TODO is a class FileType necessary for access to information about a specific type?
 *      access getFileType()->getContentDisposition OR getContentDisposition($fileType)
 * TODO different configuration structure might be more efficient
 */

use Opus\Common\Config\FileTypes;

class Application_Controller_Action_Helper_FileTypes extends Application_Controller_Action_Helper_Abstract
{
    /** @var FileTypes */
    private $fileTypes;

    /**
     * Constructor of Application_Controller_Action_Helper_FileTypes
     */
    public function __construct()
    {
        $this->fileTypes = new FileTypes();
    }

    /**
     * Returns valid MIME-Types for imports.
     *
     * TODO not used by "publish"-module yet
     *
     * @return array
     */
    public function getValidMimeTypes()
    {
        return $this->fileTypes->getValidMimeTypes();
    }

    /**
     * Checks if a MIME-type is allowed for OPUS 4 files.
     *
     * @param string      $mimeType
     * @param string|null $extension
     * @return bool
     *
     * TODO more efficient method to check?
     * TODO differentiate between extension/mime type not allowed or mime type does not match extension
     */
    public function isValidMimeType($mimeType, $extension = null)
    {
        return $this->fileTypes->isValidMimeType($mimeType, $extension);
    }

    /**
     * Returns content disposition for MIME-type used for downloads.
     *
     * @param string|null $mimeType
     * @return string
     */
    public function getContentDisposition($mimeType = null)
    {
        return $this->fileTypes->getContentDisposition($mimeType);
    }
}
