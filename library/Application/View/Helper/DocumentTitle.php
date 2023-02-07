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
use Opus\Common\Language;

/**
 * Helper for printing the title of a OPUS document.
 *
 * Which title is used can vary depending on the configuration, the selected
 * language for the user interface and options used when calling the helper.
 *
 * The document could be stored in a view variable or it could be provides as
 * a parameter.
 *
 * The document could by of type Document or it could be a result object
 * of a Solr search.
 */
class Application_View_Helper_DocumentTitle extends Application_View_Helper_Document_HelperAbstract
{
    /**
     * Prints escaped main title of document.
     *
     * @param DocumentInterface|null $document
     * @return null|string
     */
    public function documentTitle($document = null)
    {
        if ($this->isPreferUserInterfaceLanguage()) {
            $language = Language::getPart2tForPart1(Application_Translate::getInstance()->getLocale());

            $title = $document->getMainTitle($language);
        } else {
            $title = $document->getMainTitle();
        }

        if ($title !== null) {
            return htmlspecialchars($title->getValue());
        } else {
            return null;
        }
    }
}
