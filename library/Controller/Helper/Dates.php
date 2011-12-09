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
 * @version     $Id$
 */

/**
 * Controller helper for handling conversion between Opus_Date and strings.
 */
class Controller_Helper_Dates extends Zend_Controller_Action_Helper_Abstract {

    /**
     * Gets called when helper is used like method of the broker.
     * @param string $datestr Date string
     * @return
     */
    public function direct($datestr) {
        return $this->getOpusDate($datestr);
    }

    /**
     * Converts string to Opus_Date depending on current language.
     * @param string $datestr Date string
     * @return Opus_Date
     */
    public function getOpusDate($datestr) {
        $dateFormat = $this->getDateFormat();

        $date = new Zend_Date($datestr, $dateFormat);

        $dateModel = new Opus_Date();
        $dateModel->setZendDate($date);

        return $dateModel;
    }

    /**
     * Converts Opus_Date into string depending on current language.
     * @param Opus_Date $date Date string for current language
     */
    public function getDateString($date) {
        $dateFormat = $this->getDateFormat();
        $zendDate = $date->getZendDate();
        return $zendDate->get($dateFormat);
    }

    /**
     * Returns date format string for selected language of session.
     * @return string Date format string
     */
    public function getDateFormat() {
        $session = new Zend_Session_Namespace();

        $format_de = "dd.MM.yyyy";
        $format_en = "yyyy/MM/dd";

        switch($session->language) {
           case 'de':
               $format = $format_de;
               break;
           default:
               $format = $format_en;
               break;
        }

        return $format;
    }

}
