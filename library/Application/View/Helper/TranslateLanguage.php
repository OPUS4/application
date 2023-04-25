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
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * View helper for translations modifying behaviour of base class.
 */
class Application_View_Helper_TranslateLanguage extends Zend_View_Helper_Translate
{
    /**
     * Changes default behaviour of translate function to return empty string for null values.
     *
     * The default value for $messageid is changed to distinguish between null values and no
     * parameter at all. This way translate() can still be used to obtain the translation object,
     * which is the default behavior.
     *
     * Also makes sure, that first option is never interpreted as a locale. This avoids the problem
     * of a placeholder value that matches a locale, for instance a collection with the name 'de'.
     * (for more information see OPUSVIER-2546)
     *
     * TODO review if the behaviour changes are worth it - is there a better way?
     *
     * @param string $langId
     * @return string
     */
    public function translateLanguage($langId)
    {
        $translator = Application_Translate::getInstance();
        return $translator->translateLanguage($langId);
    }
}
