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
 * Abstract base class for document focused view helpers.
 */
abstract class Application_View_Helper_Document_HelperAbstract extends Application_View_Helper_Abstract
{
    /**
     * Determines if preferably the title matching the user interface language should be used.
     *
     * @var bool
     */
    private $preferUserInterfaceLanguage;

    /**
     * Returns if user interface language should be used.
     *
     * @return bool true if user interface language should be used
     */
    public function isPreferUserInterfaceLanguage()
    {
        $config = $this->getConfig();

        if ($this->preferUserInterfaceLanguage === null) {
            $this->preferUserInterfaceLanguage = isset($config->search->result->display->preferUserInterfaceLanguage) &&
                filter_var($config->search->result->display->preferUserInterfaceLanguage, FILTER_VALIDATE_BOOLEAN);
        }

        return $this->preferUserInterfaceLanguage;
    }

    /**
     * Set if user interface language should be used.
     *
     * @param bool $enabled
     */
    public function setPreferUserInterfaceLanguage($enabled)
    {
        $this->preferUserInterfaceLanguage = filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->view->result;
    }

    /**
     * @return null|DocumentInterface
     */
    public function getDocument()
    {
        $result = $this->getResult();

        if ($result !== null) {
            return $result->getDocument();
        } else {
            return null;
        }
    }
}
