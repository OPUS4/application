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
 * @copyright   Copyright (c) 2023, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\DocumentInterface;

class Oai_Model_Set_SetsManager extends Application_Model_Abstract
{
    /** @var Oai_Model_Set_SetTypeInterface[] The configured set type objects */
    private $setTypeObjects;

    /**
     * Returns all OAI sets.
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
     * Returns the matching set type class for the given set name.
     *
     * @param Oai_Model_Set_SetName $setName
     * @return Oai_Model_Set_SetTypeInterface|null
     */
    public function getSetType($setName)
    {
        $setTypes = $this->getSetTypeObjects();

        foreach ($setTypes as $setType) {
            if ($setType->supports($setName)) {
                return $setType;
            }
        }

        return null;
    }

    /**
     * Returns the configured set type objects.
     *
     * @return array|Oai_Model_Set_SetTypeInterfaceet[]
     */
    public function getSetTypeObjects()
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

                    if (
                        $setTypeClass === Oai_Model_Set_CollectionRoleSingleSet::class &&
                        isset($setTypeConfig['roleOaiName']) && ! empty($setTypeConfig['roleOaiName'])
                    ) {
                        $setTypeObjects[$setTypeName]->setRoleOaiName($setTypeConfig['roleOaiName']);
                    }

                    if (
                        $setTypeClass === Oai_Model_Set_CollectionSets::class &&
                        isset($setTypeConfig['exclude']) && ! empty($setTypeConfig['exclude'])
                    ) {
                        $setTypeObjects[$setTypeName]->setExcludedSets($setTypeConfig['exclude']);
                    }
                }
            }

            $this->setTypeObjects = $setTypeObjects;
        }

        return $this->setTypeObjects;
    }
}
