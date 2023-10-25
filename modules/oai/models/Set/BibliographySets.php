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
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\DocumentInterface;

/**
 * Class for the "bibliography" set type
 */
class Oai_Model_Set_BibliographySets extends Application_Model_Abstract implements Oai_Model_Set_SetTypeInterface
{
    /**
     * Returns sets from set type bibliography.
     *
     * @param DocumentInterface|null $document
     * @return array
     */
    public function getSets($document = null)
    {
        $bibliographySets = [
            'bibliography:true'  => 'Set for bibliographic entries',
            'bibliography:false' => 'Set for non-bibliographic entries',
        ];

        if ($document) {
            $setSpec = $document->getBelongsToBibliography() === 1 ? 'bibliography:true' : 'bibliography:false';
            return [$setSpec => $bibliographySets[$setSpec]];
        }

        return $bibliographySets;
    }

    /**
     * Configures the passed Finder according to the specified set.
     *
     * @param DocumentFinderInterface $finder
     * @param Oai_Model_Set_SetName   $setName
     * @throws Oai_Model_Exception
     */
    public function configureFinder($finder, $setName)
    {
        if ($setName->getSetPartsCount() !== 2) {
            throw new Oai_Model_Set_SetException(
                'Invalid set name: ' . implode(':', $setName->getSetParts())
            );
        }

        $setValue = $setName->getSubsetName();

        if (! in_array($setValue, ['true', 'false'])) {
            throw new Oai_Model_Set_SetException('Unknown subset: ' . $setValue);
        }

        $finder->setBelongsToBibliography((int) filter_var($setValue, FILTER_VALIDATE_BOOLEAN));
    }

    /**
     * Returns if the set type class supports the handling of given set name.
     *
     * @param Oai_Model_Set_SetName $setName
     * @return bool
     */
    public function supports($setName)
    {
        return $setName->getSetName() === 'bibliography';
    }
}
