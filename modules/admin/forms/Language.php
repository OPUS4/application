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

use Opus\Common\Language;
use Opus\Common\LanguageInterface;

class Admin_Form_Language extends Application_Form_Model_Abstract
{
    public const ELEMENT_ACTIVE  = 'Active';
    public const ELEMENT_PART2B  = 'Part2B';
    public const ELEMENT_PART2T  = 'Part2T';
    public const ELEMENT_PART1   = 'Part1';
    public const ELEMENT_SCOPE   = 'Scope';
    public const ELEMENT_TYPE    = 'Type';
    public const ELEMENT_REFNAME = 'RefName';
    public const ELEMENT_COMMENT = 'Comment';

    public function init()
    {
        parent::init();

        $this->setRemoveEmptyCheckbox(false);
        $this->setLabelPrefix('Opus_Language_');
        $this->setUseNameAsLabel(true);
        $this->setModelClass(Language::class);

        $this->addElement('checkbox', self::ELEMENT_ACTIVE);
        $this->addElement('text', self::ELEMENT_REFNAME, ['required' => true]);
        $this->addElement('text', self::ELEMENT_PART2T, ['required' => true]);
        $this->addElement('text', self::ELEMENT_PART2B);
        $this->addElement('text', self::ELEMENT_PART1);
        $this->addElement('LanguageScope', self::ELEMENT_SCOPE);
        $this->addElement('LanguageType', self::ELEMENT_TYPE);
        $this->addElement('text', self::ELEMENT_COMMENT);
    }

    /**
     * @param LanguageInterface $language
     */
    public function populateFromModel($language)
    {
        $this->getElement(self::ELEMENT_MODEL_ID)->setValue($language->getId());
        $this->getElement(self::ELEMENT_ACTIVE)->setValue($language->getActive());
        $this->getElement(self::ELEMENT_PART2B)->setValue($language->getPart2B());
        $this->getElement(self::ELEMENT_PART2T)->setValue($language->getPart2T());
        $this->getElement(self::ELEMENT_PART1)->setValue($language->getPart1());
        $this->getElement(self::ELEMENT_SCOPE)->setValue($language->getScope());
        $this->getElement(self::ELEMENT_TYPE)->setValue($language->getType());
        $this->getElement(self::ELEMENT_REFNAME)->setValue($language->getRefName());
        $this->getElement(self::ELEMENT_COMMENT)->setValue($language->getComment());
    }

    /**
     * @param LanguageInterface $language
     */
    public function updateModel($language)
    {
        $language->setActive($this->getElementValue(self::ELEMENT_ACTIVE));
        $language->setPart2B($this->getElementValue(self::ELEMENT_PART2B));
        $language->setPart2T($this->getElementValue(self::ELEMENT_PART2T));
        $language->setPart1($this->getElementValue(self::ELEMENT_PART1));
        $language->setScope($this->getElementValue(self::ELEMENT_SCOPE));
        $language->setType($this->getElementValue(self::ELEMENT_TYPE));
        $language->setRefName($this->getElementValue(self::ELEMENT_REFNAME));
        $language->setComment($this->getElementValue(self::ELEMENT_COMMENT));
    }
}
