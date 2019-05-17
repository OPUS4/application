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
 * @category    Application
 * @package     Application_View_Helper
 * @author      Maximilian Salomon <salomon@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Helper for generating Javascript providing translated messages.
 *
 * This view-helper creates a code snippet, that is able to deliver translations for javascript files. This is used
 * to provide translated message strings to Javascript code in order to display English or German messages depending
 * on the language selected by the user.
 */
class Application_View_Helper_JavascriptMessages extends Application_View_Helper_Abstract
{

    /**
     * @var array contains the messages with translation for javascript-files
     */
    private $javascriptMessages = [];

    /**
     * Indentation of generated script code.
     * @var int Integer
     */
    private $indent = 8;

    /**
     * TODO can this function be used for more?
     */
    public function javascriptMessages()
    {
        return $this;
    }

    /**
     * Adds a translation.
     *
     * If no message provided, the function tries to translate the handed key.
     *
     * @param string $key Message key
     * @param null|string $message contains an optional message
     */
    public function addMessage($key, $message = null)
    {
        if ($message != null) {
            $this->javascriptMessages[$key] = $message;
        } else {
            $this->javascriptMessages[$key] = $this->view->translate($key);
        }
    }

    public function getMessages()
    {
        return $this->javascriptMessages;
    }

    public function setMessages($value)
    {
        $this->javascriptMessages = $value;
    }

    public function getIndent()
    {
        return $this->indent;
    }

    public function setIndent($indent)
    {
        if (is_int($indent) && $indent >= 0) {
            $this->indent = $indent;
        } else {
            $this->indent = 0;
        }

        return $this;
    }

    /**
     * Renders Javascript for providing translated messages.
     * @return string Javascript snippet
     */
    public function toString() {
        $indent = str_repeat(' ', $this->getIndent());

        $output = $indent . '<script type="text/javascript">' . "\n";
        if ($this->javascriptMessages != null) {
            foreach ($this->javascriptMessages as $key => $message) {
                $output .= "$indent    opus4Messages[\"$key\"] = \"" . htmlspecialchars($message) . "\";\n";
            }
        }
        $output .= "$indent</script>\n";

        return $output;
    }

    public function __toString() {
        return $this->toString();
    }
}
