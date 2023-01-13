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
 * View helper for rendering a list of documents with checkboxes.
 *
 * The view helper needs to use additional information to determine which authors should be highlighted
 * or what additional information should be displayed.
 *
 * TODO not sure if there isn't a better solution (partial iterate document.phtml - easier customization)
 * TODO much more testing after refactoring
 */
class Application_View_Helper_FormDocuments extends Zend_View_Helper_FormElement
{
    /**
     * @param string     $name
     * @param mixed|null $value
     * @param array|null $attribs
     * @param array|null $options
     * @param string     $listsep
     * @return string
     */
    public function formDocuments($name, $value = null, $attribs = null, $options = null, $listsep = "<br />\n")
    {
        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        // @phpcs:disable
        extract($info);
        // @phpcs:enable

        if (! is_array($options)) {
            return '';
        }

        if ($value === null) {
            $value = [];
        } elseif (! is_array($value)) {
            $value = [$value];
        }

        $xhtml = "<div class=\"documents\">\n    ";

        foreach ($options as $docId => $doc) {
            // TODO use partial for defining rendering (title + authors + highlighting persons)
            $xhtml .= "<div class=\"document\">\n";

            $xhtml .= "<input name=\"$name\" class=\"document-checkbox\" type=\"checkbox\" value=\"$docId\"";

            if (in_array($docId, $value)) {
                $xhtml .= " checked=\"checked\"";
            }

            $xhtml .= " />\n";

            // TODO do not use DocumentAdapter here
            $docHelper = new Application_Util_DocumentAdapter($this->view, $doc);

            $title = $docHelper->getDocTitle();
            $year  = null; // $docHelper->getYear();

            $xhtml .= "<div class=\"document-info\">\n";

            $xhtml .= "<div class=\"document-id\">$docId</div>\n";

            $xhtml .= "<div class=\"document-title\">$title";

            if ($year !== null) {
                $xhtml .= " <span class='document-year'>($year)</span>";
            }

            $xhtml .= "</div>\n";

            $xhtml .= "<div class=\"document-authors\">";

            if (isset($attribs['person'])) {
                $personCrit = $attribs['person'];
            } else {
                $personCrit = null;
            }

            $authors = $docHelper->getAuthors(); // always an array

            foreach ($authors as $index => $author) {
                if ($index > 0) {
                    $xhtml .= "; ";
                }
                $authorName = $author['name'];

                $person = $author['person'];

                $xhtml .= "<span class=\"author";

                if ($personCrit !== null && $person->matches($personCrit)) {
                    $xhtml .= " modified";
                }

                $xhtml .= "\">";
                $xhtml .= "$authorName";
                $xhtml .= "</span>";
            }

            $xhtml .= "</div>\n";

            $xhtml .= "<div class=\"document-changes\">\n";

            $persons = $doc->getPerson();

            foreach ($persons as $person) {
                $role = $person->getRole();

                if ($role !== 'author' && $person->matches($personCrit)) {
                    $roleLabel = $this->view->translate('Opus_Person_Role_Value_' . ucfirst($role));
                    $xhtml    .= "<div class=\"document-change\"><span class=\"role\">$roleLabel</span>";
                    $xhtml    .= "<span class=\"person modified\">{$person->getName()}</span>\n";
                    $xhtml    .= "</div>\n";
                }
            }

            $xhtml .= "</div>\n";
            $xhtml .= "</div>\n";
            $xhtml .= "</div>\n";
        }

        $xhtml .= "</div>\n";

        return $xhtml;
    }
}
