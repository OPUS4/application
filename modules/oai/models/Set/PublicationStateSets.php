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
 * @copyright   Copyright (c) 2024, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Document;
use Opus\Common\DocumentFinderInterface;
use Opus\Common\DocumentInterface;
use Opus\Common\PublicationState;
use Opus\Common\Repository;

/**
 * OAI sets based on document field PublicationState.
 *
 * TODO describe requirements for class
 * TODO mapping of internal types to OAI sets (configurable?)
 * TODO need function for getting how many documents are available in different states
 */
class Oai_Model_Set_PublicationStateSets implements Oai_Model_Set_SetTypeInterface
{
    /** @var string */
    private $setName = 'status-type';

    /** @var string[] */
    private $subsetNames = [
        'draft',
        'submittedVersion',
        'acceptedVersion',
        'publishedVersion',
        'updatedVersion',
    ];

    /** @var string[] */
    private $subsetMapping = [
        PublicationState::DRAFT     => 'draft',
        PublicationState::SUBMITTED => 'submittedVersion',
        PublicationState::ACCEPTED  => 'acceptedVersion',
        PublicationState::PUBLISHED => 'publishedVersion',
        PublicationState::CORRECTED => 'updatedVersion',
        PublicationState::ENHANCED  => 'updatedVersion',
    ];

    /**
     * @param DocumentInterface|null $document
     * @return string[]
     */
    public function getSets($document = null)
    {
        $sets = [];

        if ($document !== null) {
            $publicationState = $document->getPublicationState();
            $subset           = $this->getSubsetName($publicationState);

            if ($publicationState !== null && $subset !== null) {
                // TODO check if document is visible in OAI
                $setSpec        = $this->setName . ':' . $subset;
                $sets[$setSpec] = $subset;
            }
        } else {
            $finder = Repository::getInstance()->getDocumentFinder();
            $finder->setServerState(Document::STATE_PUBLISHED);

            $states = $finder->getPublicationStateCount();
            foreach ($states as $state => $docCount) {
                $subset         = $this->getSubsetName($state);
                $setSpec        = $this->setName . ':' . $subset;
                $sets[$setSpec] = $subset;
            }
        }

        return $sets;
    }

    /**
     * @param Oai_Model_Set_SetName $setName
     * @return bool
     */
    public function supports($setName)
    {
        if ($setName->getSetName() !== $this->setName) {
            return false;
        }

        return in_array($setName->getSubsetName(), $this->getSubsetNames());
    }

    /**
     * @param DocumentFinderInterface $finder
     * @param Oai_Model_Set_SetName   $setName
     * @throws Oai_Model_Exception
     */
    public function configureFinder($finder, $setName)
    {
        $subsetName = $setName->getSubsetName();

        if (! $this->supports($setName)) {
            throw new Oai_Model_Exception(
                'The given set results in an empty list: ' . $setName->getFullSetName(),
                Oai_Model_Error::NORECORDSMATCH
            );
        }

        $publicationStates = $this->getPublicationStates($subsetName);
        $finder->setPublicationState($publicationStates);
    }

    /**
     * @return string[]
     */
    protected function getSubsetNames()
    {
        return $this->subsetNames;
    }

    /**
     * @param string $publicationState
     * @return string|null
     */
    public function getSubsetName($publicationState)
    {
        if (array_key_exists($publicationState, $this->subsetMapping)) {
            return $this->subsetMapping[$publicationState];
        } else {
            return null;
        }
    }

    /**
     * @param string $subset
     * @return string[]
     */
    public function getPublicationStates($subset)
    {
        $states = [];

        switch ($subset) {
            case 'updatedVersion':
                $states[] = PublicationState::CORRECTED;
                $states[] = PublicationState::ENHANCED;
                break;

            default:
                return array_search($subset, $this->subsetMapping);
        }

        return $states;
    }
}
