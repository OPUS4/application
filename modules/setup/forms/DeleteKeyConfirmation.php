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
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Translate\UnknownTranslationKeyException;

/**
 * Form for reseting a customized translation.
 *
 * The form shows the current and the original translation and asks the use to confirm the reset operation.
 */
class Setup_Form_DeleteKeyConfirmation extends Setup_Form_Confirmation
{
    /** @var string */
    private $translationKey;

    public function init()
    {
        parent::init();

        $this->setDecorators([
            ['ViewScript', ['viewScript' => 'language/deletekeyConfirmation.phtml']],
            'Form',
        ]);
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->translationKey = $key;
        $this->getElement(self::ELEMENT_MODEL_ID)->setValue($key);
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->translationKey;
    }

    /**
     * @return array|null
     * @throws UnknownTranslationKeyException
     */
    public function getTranslation()
    {
        $manager = new Application_Translate_TranslationManager();

        $key = $this->getKey();

        if ($key !== null) {
            return $manager->getTranslation($key);
        } else {
            return null;
        }
    }
}
