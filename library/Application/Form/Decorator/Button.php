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

class Application_Form_Decorator_Button extends Zend_Form_Decorator_Abstract
{
    /** @var string */
    private $elementName;

    /**
     * @param string $content
     * @return string
     */
    public function render($content)
    {
        $button = $this->getElement()->getElement($this->getElementName());

        if ($button === null) {
            return $content;
        }

        $buttonId       = $button->getId();
        $buttonFullName = $button->getFullyQualifiedName();
        $buttonName     = $button->getName();
        $buttonValue    = $button->getLabel();

        if ($buttonValue === null || strlen(trim($buttonValue)) === 0) {
            $buttonValue = $buttonName;
        }

        $markup  = "<div class=\"data-wrapper $buttonName-data\">";
        $markup .= "<div class=\"field\" id=\"$buttonId-element\">";
        $markup .= "<input type=\"submit\" name=\"$buttonFullName\" id=\"$buttonId\" value=\"$buttonValue\" />";
        $markup .= '</div></div>';

        return $content . $markup;
    }

    /**
     * @param string $name
     *
     * TODO BUG is this function used anywhere?
     */
    public function setElementName($name)
    {
        $this->elementName = $name;
    }

    /**
     * @return string
     */
    public function getElementName()
    {
        $name = $this->getOption('name');
        if ($name !== null) {
            $this->removeOption('name');
        } else {
            $name = $this->elementName;
        }

        return $name;
    }
}
