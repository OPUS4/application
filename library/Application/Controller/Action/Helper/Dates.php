<?PHP
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
 * @category    Application
 * @package     Controller
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Date;

/**
 * Controller helper for handling conversion between Date and strings.
 */
class Application_Controller_Action_Helper_Dates extends \Zend_Controller_Action_Helper_Abstract
{

    /**
     * Validator for dates.
     * @var Application_Form_Validate_Date
     */
    private $_validator;

    /**
     * Constructs Application_Controller_Action_Helper_Dates.
     */
    public function __construct()
    {
        $this->_validator = new Application_Form_Validate_Date();
    }

    /**
     * Gets called when helper is used like method of the broker.
     * @param string $datestr Date string
     * @return
     */
    public function direct($datestr)
    {
        return $this->getOpusDate($datestr);
    }

    /**
     * Checks if date string is valid for current locale.
     * @param string $datestr Date string
     * @return boolean TRUE - Only if date string is valid for current local
     */
    public function isValid($datestr)
    {
        $validator = new Application_Form_Validate_Date();
        return $validator->isValid($datestr);
    }

    /**
     * Converts string to Date depending on current language.
     * @param string $datestr Date string
     * @return Date
     */
    public function getOpusDate($datestr)
    {
        if (! is_null($datestr) && $this->isValid($datestr)) {
            $dateFormat = $this->_validator->getDateFormat();

            $date = new \Zend_Date($datestr, $dateFormat);

            $dateModel = new Date($date);
            return $dateModel;
        } else {
            // TODO throw exception
            return null;
        }
    }

    /**
     * Converts Date into string depending on current language.
     * @param Date $date Date
     * @return string Date string for current language
     */
    public function getDateString($date)
    {
        // Protect against invalid dates
        if (! is_null($date) && $date->isValid()) {
            $dateFormat = $this->_validator->getDateFormat();
            $zendDate = $date->getZendDate();
            return $zendDate->get($dateFormat);
        } else {
            return null;
        }
    }
}
