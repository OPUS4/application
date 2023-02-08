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
 * @copyright   Copyright (c) 2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Config;

/**
 * Helper für das Auslesen der aktuellen Opusversion vom Opus-Server.
 */
class Application_Controller_Action_Helper_Version extends Application_Controller_Action_Helper_Abstract
{
    /** @var string Version of latest OPUS 4 Release. */
    private $version;

    /**
     * @return string
     */
    public function direct()
    {
        return $this->getVersion();
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        if ($this->version === null) {
            $this->version = $this->getLatestReleaseFromServer();
        }

        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Retrieves the version of the latest release from server.
     *
     * @return string
     *
     * TODO Exception handling for connection problems (e.g. not found)
     */
    public function getLatestReleaseFromServer()
    {
        $latestUrl = Config::get()->update->latestVersionCheckUrl;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $latestUrl);
        curl_setopt($ch, CURLOPT_USERAGENT, 'OPUS 4');
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        curl_close($ch);

        if (! $response) {
            // TODO no response
            return "online check failed";
        }

        $data = json_decode($response);
        if (isset($data->tag_name)) {
            $version = $data->tag_name;
        } else {
            $version = 'unknown';
        }
        return $version;
    }
}
