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
 * @package     Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2017-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Admin_Form_Person_Changes
 *
 * TODO merge old values and changes array (only set/getChanges with all values)?
 * TODO make generic to be usable with any model (incl. excluded fields)
 * TODO work with model objects like Opus_Date for fields that use them
 */
class Admin_Form_Person_Changes extends Application_Form_Abstract
{

    private $_changes;

    private $_oldValues;

    public function init()
    {
        parent::init();

        $this->setDecorators([
            'PrepareElements',
            ['ViewScript', ['viewScript' => 'changes.phtml']]
        ]);
    }

    public function setChanges($changes)
    {
        $this->_changes = $changes;
    }

    public function getChanges()
    {
        return $this->_changes;
    }

    public function setOldValues($values)
    {
        $this->_oldValues = $values;
    }

    public function getOldValues()
    {
        return $this->_oldValues;
    }

    public function getPreparedChanges()
    {
        $oldValues = $this->getOldValues();
        $changes = $this->getChanges();

        if (!is_array($oldValues))
        {
            // TODO do some logging
            throw new InvalidArgumentException('Changes form requires old values attribute to be set');
        }

        $preparedChanges = array();

        $helper = new Application_Controller_Action_Helper_Dates();

        foreach ($oldValues as $field => $values) {
            // TODO Opus_Person specific code that needs to be removed later
            if (in_array($field, ['Id', 'OpusId'])) {
                continue;
            }

            if (stripos($field, 'date') !== false) {
                $preparedChanges[$field]['old'] = $this->forceArray($helper->getDateString(new Opus_Date($values)));
            } else {
                $preparedChanges[$field]['old'] = $this->forceArray($values);
            }

            $action = 'notmodified';

            if (array_key_exists($field, $changes)) {

                if (stripos($field, 'date') !== false) {
                    $preparedChanges[$field]['new'] = $this->forceArray($helper->getDateString(
                        new Opus_Date($changes[$field])
                    ));
                } else {
                    $preparedChanges[$field]['new'] = $this->forceArray($changes[$field]);
                }

                if (is_null($values)) {
                    $action = 'added';
                } else {
                    if (is_array($values)) {
                        $action = 'merged';
                    } else {
                        $action = 'modified';
                    }

                    if (is_null($changes[$field])) {
                        $action .= ' removed';
                    }
                }
            } else {
                $preparedChanges[$field]['new'] = $this->forceArray($values);
            }

            $preparedChanges[$field]['action'] = $action;
        }

        return $preparedChanges;
    }

    /**
     * Wraps value in array if necessary.
     * @param $values
     * @return array
     */
    public function forceArray($values)
    {
        if (is_null($values)) {
            return [];
        }
        if (is_array($values)) {
            return $values;
        } else {
            return [$values];
        }
    }
}
