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

use Opus\Common\DnbInstitute;
use Opus\Common\DnbInstituteInterface;

class Admin_Form_DnbInstitute extends Application_Form_Model_Abstract
{
    public const ELEMENT_NAME           = 'Name';
    public const ELEMENT_DEPARTMENT     = 'Department';
    public const ELEMENT_ADDRESS        = 'Address';
    public const ELEMENT_CITY           = 'City';
    public const ELEMENT_PHONE          = 'Phone';
    public const ELEMENT_DNB_CONTACT_ID = 'DnbContactId';
    public const ELEMENT_IS_GRANTOR     = 'IsGrantor';
    public const ELEMENT_IS_PUBLISHER   = 'IsPublisher';

    public function init()
    {
        parent::init();

        $this->setRemoveEmptyCheckbox(false);
        $this->setLabelPrefix('Opus_DnbInstitute_');
        $this->setUseNameAsLabel(true);
        $this->setModelClass(DnbInstitute::class);

        $fieldName       = DnbInstitute::describeField(DnbInstitute::FIELD_NAME);
        $fieldDepartment = DnbInstitute::describeField(DnbInstitute::FIELD_DEPARTMENT);

        $this->addElement('text', self::ELEMENT_NAME, [
            'required'  => true,
            'size'      => 70,
            'maxlength' => $fieldName->getMaxSize(),
        ]);
        $this->addElement('text', self::ELEMENT_DEPARTMENT, [
            'size'      => 70,
            'maxlength' => $fieldDepartment->getMaxSize(),
        ]);
        $this->addElement('textarea', self::ELEMENT_ADDRESS);
        $this->addElement('text', self::ELEMENT_CITY, ['required' => true, 'size' => 50]);
        $this->addElement('text', self::ELEMENT_PHONE);
        $this->addElement('text', self::ELEMENT_DNB_CONTACT_ID);
        $this->addElement('checkbox', self::ELEMENT_IS_GRANTOR);
        $this->addElement('checkbox', self::ELEMENT_IS_PUBLISHER);
    }

    /**
     * @param DnbInstituteInterface $institute
     */
    public function populateFromModel($institute)
    {
        $this->getElement(self::ELEMENT_MODEL_ID)->setValue($institute->getId());
        $this->getElement(self::ELEMENT_NAME)->setValue($institute->getName());
        $this->getElement(self::ELEMENT_DEPARTMENT)->setValue($institute->getDepartment());
        $this->getElement(self::ELEMENT_ADDRESS)->setValue($institute->getAddress());
        $this->getElement(self::ELEMENT_CITY)->setValue($institute->getCity());
        $this->getElement(self::ELEMENT_PHONE)->setValue($institute->getPhone());
        $this->getElement(self::ELEMENT_DNB_CONTACT_ID)->setValue($institute->getDnbContactId());
        $this->getElement(self::ELEMENT_IS_GRANTOR)->setValue($institute->getIsGrantor());
        $this->getElement(self::ELEMENT_IS_PUBLISHER)->setValue($institute->getIsPublisher());
    }

    /**
     * @param DnbInstituteInterface $institute
     */
    public function updateModel($institute)
    {
        $institute->setName($this->getElementValue(self::ELEMENT_NAME));
        $institute->setDepartment($this->getElementValue(self::ELEMENT_DEPARTMENT));
        $institute->setAddress($this->getElementValue(self::ELEMENT_ADDRESS));
        $institute->setCity($this->getElementValue(self::ELEMENT_CITY));
        $institute->setPhone($this->getElementValue(self::ELEMENT_PHONE));
        $institute->setDnbContactId($this->getElementValue(self::ELEMENT_DNB_CONTACT_ID));
        $institute->setIsGrantor($this->getElementValue(self::ELEMENT_IS_GRANTOR));
        $institute->setIsPublisher($this->getElementValue(self::ELEMENT_IS_PUBLISHER));
    }
}
