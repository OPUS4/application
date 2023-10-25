<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @copyright  Copyright (c) 2012, OPUS 4 development team
 * @license    http://www.gnu.org/licenses/gpl.html General Public License
 */

class Oai_Model_DocumentList
{
    /** @var Oai_Model_DefaultServer */
    protected $server;

    /**
     * @param Oai_Model_DefaultServer $server
     */
    public function __construct($server)
    {
            $this->server = $server;
    }

    /**
     * Retrieve all document ids for a valid oai request.
     *
     * @return array
     */
    public function query(array $oaiRequest)
    {
        $finder = $this->server->getFinder();

        if (array_key_exists('from', $oaiRequest) && ! empty($oaiRequest['from'])) {
            $from = DateTime::createFromFormat('Y-m-d', $oaiRequest['from']);
            $finder->setServerDateModifiedAfter($from->format('Y-m-d'));
        }

        if (array_key_exists('until', $oaiRequest)) {
            $until = DateTime::createFromFormat('Y-m-d', $oaiRequest['until']);
            $until->add(new DateInterval('P1D'));
            $finder->setServerDateModifiedBefore($until->format('Y-m-d'));
        }

        if (array_key_exists('set', $oaiRequest)) {
            try {
                $setsManager = $this->server->getSetsManager();
                $setName     = new Oai_Model_Set_SetName($oaiRequest['set']);
                $setType     = $setsManager->getSetType($setName);

                if ($setType) {
                    $setType->configureFinder($finder, $setName);
                } else {
                    return [];
                }
            } catch (Oai_Model_Set_SetException $e) {
                return [];
            }
        }

        return $finder->getIds();
    }
}
