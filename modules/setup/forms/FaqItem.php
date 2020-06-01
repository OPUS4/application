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
 * @package     Setup
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Setup_Form_FaqItem extends Application_Form_Translations
{

    const ELEMENT_ID = 'Id';

    const ELEMENT_NAME = 'Name';

    const ELEMENT_QUESTION = 'Question';

    const ELEMENT_ANSWER = 'Answer';

    public function init()
    {
        parent::init();

        $this->addElement('hidden', self::ELEMENT_ID);
        $this->addElement('text', self::ELEMENT_NAME, [
            'label' => 'Name', 'disabled' => true
        ]);
        $this->addElement('translation', self::ELEMENT_QUESTION, [
            'textarea' => true, 'label' => 'Question', 'cols' => 90, 'rows' => 12
        ]);
        $this->addElement('translation', self::ELEMENT_ANSWER, [
            'textarea' => true, 'label' => 'Answer', 'cols' => 90, 'rows' => 12
        ]);
    }

    public function setName($name)
    {
        $this->getElement(self::ELEMENT_ID)->setValue($name);
        $this->getElement(self::ELEMENT_NAME)->setValue($name);

        $manager = Zend_Registry::get('Zend_Translate');

        $translations = $manager->getTranslations("help_title_$name");
        $this->getElement(self::ELEMENT_QUESTION)->setValue($translations);

        $translations = $manager->getTranslations("help_content_$name");
        $this->getElement(self::ELEMENT_ANSWER)->setValue($translations);
    }

    public function addKey($key, $textaread = false, $customOptions = null)
    {
        // do nothing (not supported) TODO better solution (support specifying name of element instead of using key?)
    }

    public function updateEntry()
    {
        $database = new Opus_Translate_Dao();
        $manager = new Application_Translate_TranslationManager();

        $faqId = $this->getElementValue(self::ELEMENT_ID);
        $question = $this->getElement(self::ELEMENT_QUESTION);

        if (! $this->isArrayEmpty($question->getValue())) {
            $question->updateTranslations("help_title_$faqId");
        } else {
            $database->remove("help_title_$faqId");
        }

        $answer = $this->getElement(self::ELEMENT_ANSWER);
        if (! $this->isArrayEmpty($answer->getValue())) {
            $answer->updateTranslations("help_content_$faqId");
        } else {
            $database->remove("help_content_$faqId");
        }

        $manager->clearCache();
    }

    protected function isArrayEmpty($data)
    {
        $isEmpty = true;
        foreach ($data as $lang => $value) {
            if (! empty($value)) {
                $isEmpty = false;
                break;
            }
        }
        return $isEmpty;
    }
}
