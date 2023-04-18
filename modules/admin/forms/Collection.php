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

use Opus\Common\CollectionInterface;

/**
 * OPUSVIER-4071 Element 'Theme' is disabled, because it is currently unused.
 */
class Admin_Form_Collection extends Application_Form_Model_Abstract
{
    public const ELEMENT_NAME            = 'Name';
    public const ELEMENT_NUMBER          = 'Number';
    public const ELEMENT_VISIBLE         = 'Visible';
    public const ELEMENT_VISIBLE_PUBLISH = 'VisiblePublish';
    public const ELEMENT_OAI_SUBSET      = 'OaiSubset';
    // const ELEMENT_THEME = 'Theme';

    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();
        $this->setUseNameAsLabel(true);
        $name = $this->createElement('text', self::ELEMENT_NAME, ['size' => 70, 'required' => true]);
        $name->setValidators([new Application_Form_Validate_AtLeastOneNotEmpty(['Name', 'Number'])]);
        $name->setDescription('admin_collection_info_name_or_number_required');
        $this->addElement($name);
        $number = $this->createElement('text', self::ELEMENT_NUMBER, ['size' => 30, 'required' => true]);
        $number->setValidators([new Application_Form_Validate_AtLeastOneNotEmpty(['Name', 'Number'])]);
        $number->setDescription('admin_collection_info_name_or_number_required');
        $this->addElement($number);
        $this->addElement('checkbox', self::ELEMENT_VISIBLE);
        $this->addElement('checkbox', self::ELEMENT_VISIBLE_PUBLISH);
        $this->addElement('text', self::ELEMENT_OAI_SUBSET, ['size' => 50]);
        // $this->addElement('Theme', self::ELEMENT_THEME);
        $this->removeElement('Cancel');
    }

    /**
     * @param CollectionInterface $collection
     */
    public function populateFromModel($collection)
    {
        $this->getElement(self::ELEMENT_MODEL_ID)->setValue($collection->getId());
        $this->getElement(self::ELEMENT_NAME)->setValue($collection->getName());
        $this->getElement(self::ELEMENT_NUMBER)->setValue($collection->getNumber());
        $this->getElement(self::ELEMENT_VISIBLE)->setValue($collection->getVisible());
        $this->getElement(self::ELEMENT_VISIBLE_PUBLISH)->setValue($collection->getVisiblePublish());
        $this->getElement(self::ELEMENT_OAI_SUBSET)->setValue($collection->getOaiSubset());
        // $this->getElement(self::ELEMENT_THEME)->setValue($collection->getTheme());
    }

    /**
     * @param CollectionInterface $collection
     */
    public function updateModel($collection)
    {
        $collection->setName($this->getElementValue(self::ELEMENT_NAME));
        $collection->setNumber($this->getElementValue(self::ELEMENT_NUMBER));
        $collection->setVisible($this->getElementValue(self::ELEMENT_VISIBLE));
        $collection->setVisiblePublish($this->getElementValue(self::ELEMENT_VISIBLE_PUBLISH));
        $collection->setOaiSubset($this->getElementValue(self::ELEMENT_OAI_SUBSET));
        // $collection->setTheme($this->getElementValue(self::ELEMENT_THEME));
    }
}
