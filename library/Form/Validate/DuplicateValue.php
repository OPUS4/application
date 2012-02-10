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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Checks if in an edit form a value is selected more than once for a field.
 *
 * This form check if the same element across multiple subforms has been set to
 * the same value.
 *
 * This validator is used to check if the same language has been selected more
 * than once for document titles or abstracts. This works independent of the
 * actual document, because the POST for editing the titles contains all the
 * titles of the same type in a subform, each title in its own additional
 * subform. For instance like this:
 *
 * TitleMain form (array)
 *   +-> title main 1 form (array)
 *   +-> title main 2 form (array)
 */
class Form_Validate_DuplicateValue extends Zend_Validate_Abstract {

    /**
     * Error constant for language ID that does not exist.
     */
    const NOT_VALID = 'notValid';

    /**
     * Name of element that will be checked in subforms.
     * @var string
     */
    private $elementName;

    /**
     * Constructs a validator for duplicate entries.
     * @param type $elementName
     */
    public function __construct($elementName) {
        $this->elementName = $elementName;
    }

    /**
     * Error messages.
     *
     * TOOD figure this out
     */
    protected $_messageTemplates = array(
        self::NOT_VALID => 'admin_validate_error_language_duplicated',
    );

    /**
     * Checks if the elements of subforms have same value.
     *
     * The function assumes that the context contains multiple arrays (subforms)
     * that contain the same element.
     *
     * @param string $value Does not matter for this validator
     * @param hash $context Values of all the subforms
     */
    public function isValid($value, $context = null) {
        $value = (string) $value;
        $this->_setValue($value);

        if (!is_null($context)) {

            $selectedLanguages = array();

            foreach($context as $index => $entry) {
                if (isset($entry[$this->elementName])) {
                    $language = $entry[$this->elementName];
                    if (in_array($language, $selectedLanguages)) {
                        $this->_error(self::NOT_VALID);
                        return false;
                    }
                    else {
                        $selectedLanguages[] = $language;
                    }
                }
            }
        }

        return true;
    }

}
