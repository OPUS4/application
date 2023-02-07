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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\DocumentInterface;

/**
 * TODO this is just a start to get code out of the controller (design needs to be reconsidered)
 */
class Oai_Model_XmlFactory extends Application_Model_Abstract
{
    public const CLOSED_ACCESS = 'info:eu-repo/semantics/closedAccess';

    public const OPEN_ACCESS = 'info:eu-repo/semantics/openAccess';

    public const EMBARGOED_ACCESS = 'info:eu-repo/semantics/embargoedAccess';

    public const RESTRICTED_ACCESS = 'info:eu-repo/semantics/restrictedAccess';

    /**
     * Determines access string for 'rights' element.
     *
     * @param DocumentInterface $document
     * @return string
     *
     * TODO open access decision here independent from open access flag/collection (consolidate)
     */
    public function getAccessRights($document)
    {
        // if document is in embargo always return 'embargoedAccess' independent of files
        if (! $document->hasEmbargoPassed()) {
            return self::EMBARGOED_ACCESS;
        }

        $files = $document->getFile();

        if (count($files) > 0) {
            $restricted = false;

            foreach ($files as $file) {
                if ($file->getVisibleInFrontdoor()) {
                    if ($file->getVisibleInOai()) {
                        // if any file is accessible in frontdoor and OAI => openAccess
                        // TODO does that make sense? any file - a text file with comments, a readme
                        return self::OPEN_ACCESS;
                    } else {
                        // file is only visible in frontdoor and not in OAI => restrictedAccess
                        // TODO this case is probably more complicated (access permission)
                        $restricted = true;
                        // keep looking for openAccess file
                    }
                }
            }

            if ($restricted) {
                // no openAccess file was found, but files that are visible in frontdoor
                return self::RESTRICTED_ACCESS;
            }
        }

        // just metadata or no accessible files
        return self::CLOSED_ACCESS;
    }
}
