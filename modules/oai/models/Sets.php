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
 * @copyright   Copyright (c) 2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\CollectionRole;
use Opus\Common\Repository;

class Oai_Model_Sets extends Application_Model_Abstract
{
    public const SET_SPEC_PATTERN = '[A-Za-z0-9\-_\.!~\*\'\(\)]+';

    /**
     * Returns all oai sets.
     *
     * @return array
     */
    public function getSets()
    {
        $sets = [
            'bibliography:true'  => 'Set for bibliographic entries',
            'bibliography:false' => 'Set for non-bibliographic entries',
        ];

        $sets = array_merge(
            $sets,
            $this->getSetsForDocumentTypes(),
            $this->getSetsForCollections()
        );

        return $sets;
    }

    /**
     * Returns oai sets for document types.
     *
     * @return array
     */
    public function getSetsForDocumentTypes()
    {
        $logger         = $this->getLogger();
        $setSpecPattern = self::SET_SPEC_PATTERN;

        $sets = [];

        $dcTypeHelper = new Application_View_Helper_DcType();

        $finder = Repository::getInstance()->getDocumentFinder();
        $finder->setServerState('published');

        foreach ($finder->getDocumentTypes() as $doctype) {
            if (0 === preg_match("/^$setSpecPattern$/", $doctype)) {
                $msg = "Invalid SetSpec (doctype='" . $doctype . "')."
                    . " Allowed characters are [$setSpecPattern].";
                $logger->err("OAI-PMH: $msg");
                continue;
            }

            $dcType = $dcTypeHelper->dcType($doctype);

            $setSpec        = "doc-type:$dcType";
            $sets[$setSpec] = ucfirst($dcType);
        }

        return $sets;
    }

    /**
     * Returns oai sets for collections.
     *
     * @return array
     */
    public function getSetsForCollections()
    {
        $sets = [];

        $logger = $this->getLogger();

        $setSpecPattern = self::SET_SPEC_PATTERN;

        $oaiRolesSets = CollectionRole::fetchAllOaiEnabledRoles();

        foreach ($oaiRolesSets as $result) {
            if ($result['oai_name'] === 'doc-type') {
                continue;
            }

            if (0 === preg_match("/^$setSpecPattern$/", $result['oai_name'])) {
                $msg = "Invalid SetSpec (oai_name='" . $result['oai_name'] . "'). "
                    . " Please check collection role " . $result['id'] . ". "
                    . " Allowed characters are $setSpecPattern.";
                $logger->err("OAI-PMH: $msg");
                continue;
            }

            $setSpec = $result['oai_name'];
            // $count = $result['count'];
            $sets[$setSpec] = "Set for collection '" . $result['oai_name'] . "'";

            $sets = array_merge($sets, $this->getSetsForCollectionRole($setSpec, $result['id']));
        }

        return $sets;
    }

    /**
     * Returns sets for collections of a collection role.
     *
     * @param string $setSpec OAI name for collection role
     * @param int    $roleId int Database ID of role
     * @return array
     */
    public function getSetsForCollectionRole($setSpec, $roleId)
    {
        $logger = $this->getLogger();

        $sets = [];

        $setSpecPattern = self::SET_SPEC_PATTERN;

        $role = CollectionRole::get($roleId);
        foreach ($role->getOaiSetNames() as $subset) {
            $subSetSpec = "$setSpec:" . $subset['oai_subset'];
            // $subSetCount = $subset['count'];

            if (0 === preg_match("/^$setSpecPattern$/", $subset['oai_subset'])) {
                $msg = "Invalid SetSpec (oai_name='" . $subset['oai_subset'] . "')."
                    . " Please check collection " . $subset['id'] . ". "
                    . " Allowed characters are [$setSpecPattern].";
                $logger->err("OAI-PMH: $msg");
                continue;
            }

            $sets[$subSetSpec] = "Subset '" . $subset['oai_subset'] . "'"
                . " for collection '" . $setSpec . "'"
                . ': "' . trim($subset['name']) . '"';
        }

        return $sets;
    }
}
