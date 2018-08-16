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
 * @package     Form_Validate
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2017-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Application_Form_Validate_uploadFilenameCheck validates the filename of an fileupload.
 */
class Application_Form_Validate_Filename extends Zend_Validate_Abstract
{
    protected $filenameMaxLength = '';
    protected $filenameFormat = '';

    /**
     * Error message key for invalid filename length
     */
    const MSG_NAME_LENGTH = 'namelength';

    /**
     * Error message key for malformed filename
     */
    const MSG_NAME_FORMAT = 'format';

    /**
     * Errormessage Templates
     * @var array
     */
    protected $_messageTemplates = [
        self::MSG_NAME_LENGTH => "filenameLengthError",
        self::MSG_NAME_FORMAT => "filenameFormatError"
    ];

    /**
     * variables for messageTemplates
     * @var array
     */
    protected $_messageVariables = array(
        'size' => '_filenameMaxLength',
    );

    /**
     * Application_Form_Validate_Filename constructor.
     * @param $options
     */
    public function __construct($options)
    {
        $this->_filenameMaxLength = $options[0];
        $this->_filenameFormat = '/' . $options[1] . '/';
    }

    /**
     * Check the size and the format of a filename.
     *
     * @param mixed $value
     * @param null $file
     * @return bool
     */
    public function isValid($value, $file = null)
    {
        $this->_setValue($value);
        if ($file !== null) {
            $data['filename'] = $file['name'];
        } else {
            $data = pathinfo($value);
            if (!array_key_exists('filename', $data)) {

                return false;
            }
        }

        if (strlen($data['filename']) > $this->_filenameMaxLength) {
            $this->_error(self::MSG_NAME_LENGTH);
            return false;
        }

        if (preg_match($this->_filenameFormat, $data['filename']) === 0) {
            $this->_error(self::MSG_NAME_FORMAT);
            return false;
        }

        return true;
    }

}