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
 *
 * idea and template at http://everflux.de/zend-framework-ein-eigener-validator-601/
 *
 * @category    Application
 * @package     Module_Frontdoor
 * @author      Tobias Leidinger <tobias.leidinger@googlemail.com>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
require_once 'Zend/Validate/Abstract.php';


/**
 * validator class to check if at least one of the given fields is not empty
 */
class Frontdoor_Form_AtLeastOneValidator extends Zend_Validate_Abstract
{
    const REQUIRED_EMPTY = 'requiredFieldsEmpty';

    protected $_messageTemplates = array(
        self::REQUIRED_EMPTY => 'At least one of the checkboxes must be checked'
    );

    private $requiredFields;
    private $requiredFieldKeys;

    function __construct()
    {
        $this->requiredFields = array();
        $this->requiredFieldKeys = array();

    }

    public function addField(&$field)
    {
        $this->requiredFieldKeys[] = $field->getName();
        $this->requiredFields[] = &$field;
    }

    public function isValid($value, $context = null)
    {
        $size = count($this->requiredFieldKeys);
        //  if no fields required, return success
        if ($size <= 0)
        {
            return true;
        }

        $result = false;

        if (is_array($context))
        {
            $empty = true;

            foreach (array_keys($context) as $field)
            {
                if (in_array($field, $this->requiredFieldKeys) )
                {
                    if (! empty($context[$field]))
                    {
                        $empty = false;
                        break;
                    }
                }
            }

            if ($empty)
            {
                $this->_error(self::REQUIRED_EMPTY);

                $result = false;
            }
            else {
                $result = true;
            }
        }
        
        return $result;
    }
}
?>