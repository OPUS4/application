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
 * @package     Import
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2016, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class for validating OPUS import xml.
 *
 */
class Application_Import_XmlValidation extends Application_Model_Abstract {

    private $errors;

    /**
     * Validates import XML based on schema.
     *
     * Can validate DOMDocument objects or XML provided as string.
     *
     * @param $xml DOMDocument|string
     */
    public function validate($xml) {
        $this->errors = null;

        // TODO check for null|empty

        if (!$xml instanceof DOMDocument) {
            $xml = $this->getDocument($xml);
        }

        libxml_clear_errors();
        libxml_use_internal_errors(true);

        $valid = $xml->schemaValidate(__DIR__ . DIRECTORY_SEPARATOR . 'opus-import.xsd');        

        $this->errors = libxml_get_errors();                
        libxml_clear_errors();
        libxml_use_internal_errors(false);

        return $valid;
    }

    public function getErrors() {
        return $this->errors;
    }

    private function getDocument($xml) {
        libxml_clear_errors();
        libxml_use_internal_errors(true);

        $doc = new DOMDocument();

        $doc->loadXML($xml);

        // TODO error processing

        libxml_use_internal_errors(false);
        libxml_clear_errors();

        return $doc;
    }
    
    public function getErrorsPrettyPrinted() {
        $errorMsg = '';
        foreach ($this->errors as $error) {
            $errorMsg .= "\non line $error->line ";
            switch ($error->level) {
                case LIBXML_ERR_WARNING:
                    $errorMsg .= "(Warning $error->code): ";
                    break;
                case LIBXML_ERR_ERROR:
                    $errorMsg .= "(Error $error->code): ";
                    break;
                case LIBXML_ERR_FATAL:
                    $errorMsg .= "(Fatal Error $error->code): ";
                    break;
            }
            $errorMsg .= trim($error->message);
        }
        return $errorMsg;
    }

}