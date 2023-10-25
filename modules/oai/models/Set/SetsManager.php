<?php

use Opus\Common\DocumentInterface;

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
 * @copyright   Copyright (c) 2023, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Oai_Model_Set_SetsManager extends Application_Model_Abstract
{
    /** @var Oai_Model_Set_SetTypeInterface[] The configured set type objects */
    private $setTypeObjects;

    /**
     * Returns all oai sets.
     *
     * @param DocumentInterface|null $document
     * @return array
     */
    public function getSets($document = null)
    {
        $sets = [];

        foreach ($this->getSetTypeObjects() as $setTypeSets) {
            $sets = array_merge($sets, $setTypeSets->getSets($document));
        }

        return $sets;
    }

    /**
     * @param Oai_Model_Set_SetName $setName
     * @return Oai_Model_Set_SetTypeInterface|null
     */
    public function getSetType($setName)
    {
        $setTypes    = $this->getSetTypeObjects();
        $setTypeName = $setName->getSetTypeName();

        if (array_key_exists($setTypeName, $setTypes) && $setTypeName !== 'collection') {
            return $setTypes[$setTypeName];
        } elseif (array_key_exists('collection', $setTypes)) {
            return $setTypes['collection'];
        }

        return null;
    }

    /**
     * Returns the configured set type objects.
     *
     * @return array|Oai_Model_Set_SetTypeInterfaceet[]
     */
    private function getSetTypeObjects()
    {
        if ($this->setTypeObjects === null) {
            $this->setTypeObjects = [];
            $oaiConfig            = Oai_Model_OaiConfig::getInstance();
            $setTypes             = $oaiConfig->getSetTypes();

            $setTypeObjects = [];

            foreach ($setTypes as $setTypeName => $setTypeConfig) {
                $setTypeClass = $setTypeConfig['class'] ?? '';
                if (class_exists($setTypeClass)) {
                    $setTypeObjects[$setTypeName] = new $setTypeClass();
                }
            }

            $this->setTypeObjects = $setTypeObjects;
        }

        return $this->setTypeObjects;
    }
}
