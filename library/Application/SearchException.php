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

use Opus\Search\SearchException;

class Application_SearchException extends Application_Exception
{
    /**
     * @param SearchException $exception
     * @param bool            $usePlainMessage set to true if exception message should not be translated
     */
    public function __construct($exception, $usePlainMessage = false)
    {
        parent::__construct($exception->getMessage(), $exception->getCode(), $exception->getPrevious());

        if ($exception->isServerUnreachable()) {
            if ($usePlainMessage) {
                $this->message = 'search server is not responding -- try again later';
            } else {
                $this->message = 'error_search_unavailable';
            }
            $this->setHttpResponseCode(503);
            return;
        }

        if ($exception->isInvalidQuery()) {
            if ($usePlainMessage) {
                $this->message = 'search query is invalid -- check syntax';
            } else {
                $this->message = 'error_search_invalidquery';
            }
            $this->setHttpResponseCode(500);
            return;
        }

        if ($usePlainMessage) {
            $this->message = 'unknown error while executing search query';
        } else {
            $this->message = 'error_search_unknown';
        }
        $this->setHttpResponseCode(500);
    }
}
