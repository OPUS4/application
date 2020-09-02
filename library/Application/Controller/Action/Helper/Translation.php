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
 * @package     Controller
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Helper for handling translations.
 *
 * This class keeps some of the special code generating translation keys out of
 * the controllers and view scripts.
 */
class Application_Controller_Action_Helper_Translation extends Zend_Controller_Action_Helper_Abstract
{

    /**
     * Gets called when the helper is used like a method of the broker.
     *
     * @param string $modelName
     * @param string $fieldName
     * @param string $value
     * @return string
     */
    public function direct($modelName, $fieldName, $value)
    {
        return $this->getKeyForValue($modelName, $fieldName, $value);
    }

    /**
     * Returns translation key for a value of a selection field.
     * @param string $modelName
     * @param string $fieldName
     * @param string $value
     * @return string Translation key
     */
    public function getKeyForValue($modelName, $fieldName, $value)
    {
        // The 'Type' and the 'Language' field of Opus_Document currently need
        // to be handled separately, since their key don't have a prefix.
        if ($modelName === 'Opus_Document'
                && ($fieldName === 'Language'
                        || $fieldName === 'Type'
                        || $fieldName === 'PublicationState')) {
            return $value;
        } elseif ($modelName === 'Opus_Enrichment' && $fieldName === 'KeyName') {
            return $value;
        } else {
            return $modelName . '_' . $fieldName . '_Value_' . ucfirst($value);
        }
    }

    /**
     * Returns translation key for a field.
     *
     * Currently the names of the fields are used as key, except for the 'Type'
     * fields which are present in multiple models.
     *
     * @param string $modelName
     * @param string $fieldName
     * @return string Translation key
     */
    public function getKeyForField($modelName, $fieldName)
    {
        if ($fieldName === 'Type') {
            return $modelName . '_' . $fieldName;
        } else {
            switch ($modelName) {
                case 'Opus_Language':
                    return $modelName . '_' . $fieldName;
                default:
                    return $fieldName;
            }
        }
    }
}
