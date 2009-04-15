<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @category   Application
 * @package    Module_Webapi
 * @author     Henning Gerhardt (henning.gerhardt@slub-dresden.de)
 * @copyright  Copyright (c) 2009, OPUS 4 development team
 * @license    http://www.gnu.org/licenses/gpl.html General Public License
 * @version    $Id$
 */

/**
 * General class for responding webapi informations.
 */
class Response {

    /**
     * Holds webapi host name.
     *
     * @var string
     */
    protected $_hostname = '';

    /**
     * Holds webapi protocol schema.
     *
     * @var string
     */
    protected $_protocol = 'http://';

    /**
     * Holds response code information.
     *
     * @var int
     */
    protected $_responseCode = 200;

    /**
     * Holds a XML DOMDocument.
     *
     * @var DOMDocument
     */
    protected $_xml = '';

    /**
     * Contruction stuff.
     */
    public function __construct() {
        // TODO: find a better Zend way of life
        $this->_hostname = $_SERVER['HTTP_HOST'];

        $this->_xml = new DOMDocument('1.0', 'utf-8');
        $this->_xml->formatOutput = true;
    }

    /**
     * Returns setted response code.
     * Default response code is set to 200.
     *
     * @return int
     */
    public function getResponseCode() {
        return $this->_responseCode;
    }

    /**
     * Set a new response code.
     *
     * @param int $codeNumer Response code number to set.
     * @return void
     */
    public function setResponseCode($codeNumer) {
        if ((true === is_int($codeNumer)) and ($codeNumer > 0)) {
            $this->_responseCode = $codeNumer;
        }
    }
}
