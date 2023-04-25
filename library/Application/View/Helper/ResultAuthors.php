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
 * @copyright   Copyright (c) 2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Helper for printing the year of a OPUS document in search results.
 *
 * TODO configure separator
 * TODO configure prefix/suffix
 * TODO create function getResult in parent class
 */
class Application_View_Helper_ResultAuthors extends Application_View_Helper_Document_HelperAbstract
{
    /** @var string */
    private $separator = ' ; ';

    /**
     * Prints escaped main title of document.
     *
     * @return null|string
     */
    public function resultAuthors()
    {
        $result = $this->getResult();

        $output = '';

        foreach ($result->getAsset('author') as $authorIndex => $author) {
            $authorStr = rtrim($author, ', '); // TODO should not be necessary (OPUSVIER-3891)

            // TODO put somewhere else -> view helper for author search ?
            $authorSearch           = $this->view->authorSearch;
            $authorSearch['author'] = $authorStr;
            $authorSearchUrl        = $this->view->url($authorSearch, null, true);

            if ($authorIndex !== 0) {
                $output .= $this->separator;
            }

            $authorOutput = htmlspecialchars($authorStr);

            $output .= "<a href=\"$authorSearchUrl\">$authorOutput</a>";
        }

        return $output;
    }

    /**
     * @param string $separator
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;
    }
}
