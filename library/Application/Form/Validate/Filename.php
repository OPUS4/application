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
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Application_Form_Validate_uploadFilenameCheck validates the filename.
 *
 * The regular expression used to validate a filename must be specified without the usual delimiters. Bracket style
 * delimiters '<>' will be added automatically.
 *
 * TODO don't add delimiter internally - just accept a complete regular expression
 */
class Application_Form_Validate_Filename extends Zend_Validate_Abstract
{
    /**
     * @var int maximal filename length
     */
    protected $filenameMaxLength = 0;

    /**
     * @var string the format is a empty string. If this is used as regex, it matches always.
     */
    protected $filenameFormat = '';

    /**
     * Error message key for invalid filename length
     */
    const MSG_NAME_LENGTH = 'nameLength';

    /**
     * Error message key for malformed filename
     */
    const MSG_NAME_FORMAT = 'format';

    /**
     * Error message Templates
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
        'size' => 'filenameMaxLength',
    );

    /**
     * Application_Form_Validate_Filename constructor.
     * @param $options
     */
    public function __construct($options = null)
    {
        if (isset($options['filenameMaxLength'])) {
            $this->setFilenameMaxLength($options['filenameMaxLength']);
        }
        if (isset($options['filenameFormat'])) {
            $this->setFilenameFormat($options['filenameFormat']);
        }
    }

    public function getFilenameMaxLength()
    {
        return $this->filenameMaxLength;
    }

    public function getFilenameFormat()
    {
        return $this->filenameFormat;
    }

    public function setFilenameMaxLength($value)
    {
        $this->filenameMaxLength = $value;
    }

    public function setFilenameFormat($value)
    {
        if ($this->validateFilenameFormat("<$value>") == false) {
            $this->filenameFormat = null;
        } else {
            $this->filenameFormat = $value;
        }
    }

    /**
     * TODO: Use Logging-Trait (introduced in OPUS 4.7)
     *
     * Verifies that the regular expression is generally valid.
     *
     * @param $value -> a regular expression, which is validated here.
     * @return bool
     */
    public function validateFilenameFormat($value)
    {
        $logger = Application_Configuration::getInstance()->getLogger();

        if (@preg_match($value, null) === false) {
            $logger->warn('Your regular expression for your filename-validation is not valid.');
            return false;
        }

        return true;
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

        if (strlen($data['filename']) > $this->filenameMaxLength and $this->filenameMaxLength != 0) {
            $this->_error(self::MSG_NAME_LENGTH);
            return false;
        }

        $pattern = "<$this->filenameFormat>";

        if (preg_match($pattern, $data['filename']) === 0) {
            $this->_error(self::MSG_NAME_FORMAT);
            return false;
        }

        return true;
    }
}
