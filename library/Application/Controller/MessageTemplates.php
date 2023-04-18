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

/**
 * Klasse für das Verwalten von Nachrichten.
 *
 * Die Klasse kann mit einem Array von Standardnachrichten instanziert werden. Danach können diese Nachrichten
 * modifiziert werden. Ein einmal angelegter Nachrichtenschlüssel kann aber nicht auf null gesetzt werden, um
 * sicherzustellen, daß kein Nachrichtenschlüssel jemals fehlt.
 */
class Application_Controller_MessageTemplates
{
    /**
     * Nachrichten.
     *
     * Die Nachrichten sind ein Array mit Schlüssel und Werten. Die Werte können Strings sein, oder Arrays wie in den
     * folgenden Beispielen.
     *
     * <pre>
     * array(
     *     'successKey' => 'success_message'
     *     'failureKey' => array(
     *         'failure' => 'failure_message'
     * )
     * </pre>
     *
     * Dadurch können die Nachrichten direkt mit unseren Redirect-Funktionen und dem FlashMessenger eingesetzt werden.
     *
     * @var array
     */
    private $messages;

    /**
     * Konstruiert Instanz mit Basisnachrichten.
     *
     * @param array $messages
     */
    public function __construct($messages)
    {
        if ($messages === null || ! is_array($messages)) {
            throw new Application_Exception(__METHOD__ . ' Parameter \'messages\' is required and must be an array.');
        }

        $this->messages = $messages;
    }

    /**
     * Liefert Array mit allen Nachrichten.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Setzt mehrere Nachrichten.
     *
     * @param array $messages
     */
    public function setMessages($messages)
    {
        if (is_array($messages)) {
            foreach ($messages as $key => $message) {
                $this->setMessage($key, $message);
            }
        }
    }

    /**
     * Liefert eine Nachricht.
     *
     * @param string $key Schlüssel für Nachricht
     * @return string
     * @throws Application_Exception
     */
    public function getMessage($key)
    {
        if (array_key_exists($key, $this->messages)) {
            return $this->messages[$key];
        } else {
            throw new Application_Exception("Message key '$key' is not defined.");
        }
    }

    /**
     * Setzt Nachricht für Schlüssel.
     *
     * Wenn der Schlüssel noch nicht existiert wird er hinzugefügt.
     *
     * @param string $key Nachrichtenschlüssel
     * @param string $message Nachricht
     */
    public function setMessage($key, $message)
    {
        if (! is_array($this->messages)) {
            $this->messages = [];
        }

        if ($message === null) {
            throw new Application_Exception("Message key '$key' must not be null.");
        }

        $this->messages[$key] = $message;
    }
}
