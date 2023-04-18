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
 * @copyright   Copyright (c) 2016, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * View helper for translations modifying behaviour of base class.
 */
class Application_View_Helper_Translate extends Zend_View_Helper_Translate
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
     * TODO replace parent function entirely? Doing basically same things twice is not very efficient.
     * TODO really try to get rid of this
     * TODO review if the behaviour changes are worth it - is there a better way?
     *
     * @param float|string|null $messageid
     * @return parent|string
     */
    public function translate($messageid = -1.1)
    {
        if ($messageid === null) {
            return '';
        } elseif ($messageid === -1.1) {
            return $this;
        }

        $options = func_get_args();
        array_shift($options);

        $optCount = count($options);

        $locale = null;

        if (($optCount > 1) && Zend_Locale::isLocale($options[$optCount - 1])) {
            $locale = array_pop($options);
        }

        if (($optCount > 0) && is_array($options[0]) === true) {
            $options = $options[0];
        }

        $translate = $this->getTranslator();

        if ($translate !== null) {
            $messageid = $translate->translate($messageid, $locale);
        }

        if (count($options) === 0) {
            return $messageid;
        }

        return vsprintf($messageid, $options);
    }
}
