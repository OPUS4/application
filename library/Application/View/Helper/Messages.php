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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * View helper for rendering response messages.
 */
class Application_View_Helper_Messages extends Application_View_Helper_Abstract
{

    /**
     * Returns HTML for messages.
     */
    public function messages($messages = null)
    {
        if (!is_array($messages)) {
            $messages = $this->getMessages();
        }

        $output = '';

        if (!empty($messages)) {
            $output .= '<div class="messages">' . PHP_EOL;

            foreach ($messages as $message) {
                if (is_array($message) && array_key_exists('message', $message)) {
                    $translated = htmlspecialchars($this->view->translate($message['message']));
                    $level = array_key_exists('level', $message) ? $message['level'] : '';

                    $output .= "  <div class=\"$level\">$translated</div>" . PHP_EOL;
                }
                else {
                    $translated = htmlspecialchars($this->view->translate($message));
                    $output .= "  <div>$translated</div>" . PHP_EOL;
                }
            }

            $output .= '</div>' . PHP_EOL;
        }

        return $output;
    }

    public function getMessages()
    {
        return !is_null($this->view->flashMessenger) ? $this->view->flashMessenger->getMessages() : null;
    }
}
