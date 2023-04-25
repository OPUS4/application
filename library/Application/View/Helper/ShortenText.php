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
 * Shortens texts to a maximum length while not cutting in the middle of words.
 */
class Application_View_Helper_ShortenText extends Application_View_Helper_Abstract
{
    /** @var int */
    private $maxLength;

    /**
     * Shortens text while not cutting in the middle of words.
     *
     * TODO the string '. ' should be handled like a space after the last word
     * TODO how to handle opened HTML tags in shortened text
     *
     * @param string $text
     * @return string Shortened text
     */
    public function shortenText($text)
    {
        $maxLength = $this->getMaxLength();

        if (strlen($text) > $maxLength) {
            $shortText = mb_substr($text, 0, $maxLength);

            if (! ctype_space(mb_substr($text, $maxLength, 1))) {
                $lastSpace = mb_strrpos($shortText, ' ');

                $shortText = mb_substr($shortText, 0, $lastSpace);
            }
        } else {
            $shortText = $text;
        }

        return trim($shortText);
    }

    /**
     * Returns maximum length of text.
     *
     * @return int
     */
    public function getMaxLength()
    {
        if ($this->maxLength === null) {
            $config = $this->getConfig();

            $maxLength = 0;

            if ($config !== null && isset($config->frontdoor->numOfShortAbstractChars)) {
                $maxLength = trim($config->frontdoor->numOfShortAbstractChars);
            }

            $this->setMaxLength($maxLength);
        }

        return $this->maxLength;
    }

    /**
     * Sets maximum length of text.
     *
     * @param int $length
     */
    public function setMaxLength($length)
    {
        if (is_int($length)) {
            $this->maxLength = $length;
        } elseif (is_string($length) && ctype_digit($length)) {
            $this->maxLength = intval($length);
        } else {
            $this->maxLength = 0;
        }
    }
}
