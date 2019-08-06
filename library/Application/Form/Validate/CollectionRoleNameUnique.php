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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Application_Form_Validate_CollectionRoleNameUnique extends Zend_Validate_Abstract
{

    const NAME_NOT_UNIQUE = 'notUnique';

    protected $_messageTemplates = [
        self::NAME_NOT_UNIQUE => 'admin_collectionroles_error_not_unique'
    ];

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @return boolean
     * @throws Zend_Validate_Exception If validation of $value is impossible
     */
    public function isValid($value, $context = null)
    {
        $value = (string) $value;

        $this->_setValue($value);

        if (! is_null($context) && is_array($context) && array_key_exists('Id', $context)) {
            $collectionId = $context['Id'];
        } else {
            $collectionId = 0;
        }

        $model = $this->_getModel($value);

        if (! is_null($model) && $model->getId() != $collectionId) {
            // es gibt bereits CollectionRole mit Identifier (z.B. Name) und anderer ID
            $this->_error(self::NAME_NOT_UNIQUE);
            return false;
        }

        return true;
    }

    /**
     * Holt CollectionRole mit Identifier.
     * @param $identifier
     * @return Opus_CollectionRole
     */
    protected function _getModel($identifier)
    {
        return Opus_CollectionRole::fetchByName($identifier);
    }
}
