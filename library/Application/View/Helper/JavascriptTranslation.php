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
 * @package     View_Helper_Document_Helper
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Application_View_Helper_JavascriptTranslation
 * This view-helper creates a code snippet, what is able to deliver the translations for javascript files.
 * With addTranslation, there is an possibility to add an translation-key to the code snippet.
 * The key will be translated and the message will be delivered. It is also possible to insert your own key-message pair.
 */
class Application_View_Helper_JavascriptTranslation extends Application_View_Helper_Abstract
{
    private $translations = [];

    public function javascriptTranslation()
    {
        $output = '<script type="text/javascript">' . "\n";
        if ($this->translations != null) {
            foreach ($this->translations as $key => $message) {
                $output .= "            " . "messages[\"$key\"] = \"" . htmlspecialchars($message) . "\";" . "\n";
            }
        }
        $output .= "        " . "</script>" . "\n";

        return $output;
    }

    public function addTranslation($key, $message = null)
    {
        if ($message != null) {
            $this->translations[$key] = $message;
        } else {
            $message = $this->view->translate($key);
            $this->translations[$key] = $message;
        }
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    public function setTranslations($value)
    {
        $this->translations = $value;
    }
}
