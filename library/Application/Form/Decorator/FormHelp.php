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
 * Decorator that renders a text block in a form.
 *
 * The text block can be used for static information to help users with the form.
 *
 * TODO make tag more configurable
 */
class Application_Form_Decorator_FormHelp extends Zend_Form_Decorator_Abstract
{
    /**
     * @var string
     * @phpcs:disable
     */
    protected $_placement = 'PREPEND';
    // @phpcs:enable

    /** @var string */
    protected $cssClass = 'form-help';

    /**
     * @param string $content
     * @return string
     */
    public function render($content)
    {
        $xhtml = $this->renderMessage();

        if ($this->getPlacement() === self::APPEND) {
            $xhtml = $content . $xhtml;
        } else {
            $xhtml .= $content;
        }

        return $xhtml;
    }

    /**
     * @return string
     */
    public function renderMessage()
    {
        $message = $this->getOption('message');

        $xhtml = '';

        if ($message !== null) {
            $translator = $this->getElement()->getTranslator();

            $cssClass = $this->getClass();

            $xhtml = "<div class=\"$cssClass\">";

            if ($translator !== null) {
                $xhtml .= $translator->translate($message);
            } else {
                $xhtml .= $message;
            }

            $xhtml .= '</div>';
        }

        return $xhtml;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        $cssClass = $this->cssClass;

        $classOption = $this->getOption('class');
        if ($classOption !== null) {
            $cssClass = $classOption;
            $this->removeOption('class');
        }

        $this->cssClass = $cssClass;

        return $cssClass;
    }
}
