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

/**
 * Add short names for CC 3.0 licences.
 */

use Opus\Common\Licence;
use Opus\Common\Repository;
use Opus\Model\Plugin\InvalidateDocumentCache;

/**
 * Added short names (labels) to licences that look like the old standard licences distributed with OPUS 4.
 */
class Application_Update_AddCC30LicenceShortNames extends Application_Update_PluginAbstract
{
    /**
     * Patterns for licence matching.
     *
     * @var array
     */
    private $licences = [
        '/(?:(Creative Commons)|(CC)).*Namensnennung.*Nicht.*kommerziell.*Keine.*Bearbeitung/'                                => 'CC BY-NC-ND 3.0',
        '/(?:(Creative Commons)|(CC)).*Namensnennung.*Keine.*kommerzielle Nutzung.*Weitergabe.*unter.*gleichen.*Bedingungen/' => 'CC BY-NC-SA 3.0',
        '/(?:(Creative Commons)|(CC)).*Namensnennung.*Nicht.*kommerziell.*Weitergabe.*unter.*gleichen.*Bedingungen/'          => 'CC BY-NC-SA 3.0',
        '/(?:(Creative Commons)|(CC)).*Namensnennung.*Nicht.*kommerziell/'                                                    => 'CC BY-NC 3.0',
        '/(?:(Creative Commons)|(CC)).*Namensnennung.*Keine.*Bearbeitung/'                                                    => 'CC BY-ND 3.0',
        '/(?:(Creative Commons)|(CC)).*Namensnennung.*Weitergabe.*unter.*gleichen.*Bedingungen/'                              => 'CC BY-SA 3.0',
        '/(?:(Creative Commons)|(CC)).*Namensnennung/'                                                                        => 'CC BY 3.0',
    ];

    public function run()
    {
        $cache = Repository::getInstance()->getDocumentXmlCache();

        $licences = Licence::getAll();

        foreach ($licences as $licence) {
            $nameLong = $licence->getNameLong();

            $name = $licence->getName();

            if ($name !== null) {
                $this->log("Licence already has short name ('$name' => '$nameLong')");
                continue;
            }

            if (strpos($nameLong, '4.0') !== false) {
                $this->log("Not adding label to 4.0 licence ($nameLong)");
                continue;
            }

            $name = $this->getShortName($nameLong);

            if ($name !== null) {
                // 'name' must be unique - check if already used
                $existingLicence = Licence::fetchByName($name);

                if ($existingLicence === null) {
                    $licence->setName($name);

                    // prevent updates to ServerDateModified
                    // TODO cache should be transparent - updating ServerDateModified is important
                    $licence->unregisterPlugin(InvalidateDocumentCache::class);

                    $licence->store();

                    // since cache updates were prevented a manual refresh is necessary
                    $cache->removeAllEntriesForDependentModel($licence);

                    $this->log("Setting label '$name' for licence '{$nameLong}'.");

                    $this->removeLicence($name);
                }
            } else {
                $this->log("No label found for licence '$nameLong'");
            }
        }

        $this->log('Please verify licence names in the administration.');
    }

    /**
     * Get matching short name for long licence names.
     *
     * @param string $nameLong
     * @return string|null
     */
    public function getShortName($nameLong)
    {
        foreach ($this->licences as $long => $short) {
            if (preg_match($long, $nameLong) > 0) {
                return $short;
            }
        }

        return null;
    }

    /**
     * Removes licence after it has been used.
     *
     * @param string $name
     */
    public function removeLicence($name)
    {
        if (($key = array_search($name, $this->licences)) !== false) {
            unset($this->licences[$key]);
        }
    }
}
