<?php
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
 * @package     Form_Validate
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2013-2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Log;

/**
 * Prüft ob ein Wertfür ein Feld in Unterformularen mindestens einmal vorkommt.
 *
 * Wird für die Prüfung verwendet, ob ein TitleMain in der Dokumentensprache vorliegt.
 */
class Application_Form_Validate_ValuePresentInSubforms extends \Zend_Validate_Abstract
{

    /**
     * Error constant for language ID that does not exist.
     */
    const NOT_VALID = 'notValid';

    /**
     * Name des Formularelements das geprüft werden soll.
     */
    private $_elementName;

    /**
     * Error messages.
     */
    protected $_messageTemplates = [
        self::NOT_VALID => 'admin_validate_error_value_duplicated',
    ];

    /**
     * Konstruiert Instanz des Validators.
     * @param string $elementName
     */
    public function __construct($elementName)
    {
        $this->_elementName = $elementName;
    }

    /**
     * Führt Validierung aus.
     *
     * Wenn kein Name für das Element ($this->elementName) spezifiziert wurde oder kein Kontext ($context) übergeben
     * wurde, schlägt die Validierung fehl, da nicht geprüft werden, daß der Wert in den Unterformularen vorkommt.
     *
     * @param array $value
     * @param array $context
     * @return boolean TRUE - wenn der Wert in den Unterformularen vorkommt; FALSE - wenn er nicht vorkommt
     */
    public function isValid($value, $context = null)
    {
        $value = (string) $value;
        $this->_setValue($value);

        if (! is_null($context) && count(trim($this->_elementName)) !== 0) {
            foreach ($context as $index => $entry) {
                if (isset($entry[$this->_elementName]) && $entry[$this->_elementName] == $value) {
                    return true;
                }
            }
        } else {
             Log::get()->err(__CLASS__ . '::' . __METHOD__ . ' mit $context = null aufgerufen.');
        }

        $this->_error(self::NOT_VALID);
        return false;
    }

    /**
     * Liefert den Namen des Elements, dass geprüft werden soll.
     * @return string
     */
    public function getElementName()
    {
        return $this->_elementName;
    }
}
