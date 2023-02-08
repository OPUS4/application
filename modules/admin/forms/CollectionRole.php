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
 *
 * TODO OaiName could be optional since it is usually the same as Name (which could be used as default)
 */

use Opus\Common\CollectionRole;
use Opus\Common\CollectionRoleInterface;

class Admin_Form_CollectionRole extends Application_Form_Model_Abstract
{
    public const ELEMENT_NAME                   = 'Name';
    public const ELEMENT_OAI_NAME               = 'OaiName';
    public const ELEMENT_DISPLAYNAME            = 'DisplayName';
    public const ELEMENT_POSITION               = 'Position';
    public const ELEMENT_VISIBLE                = 'Visible';
    public const ELEMENT_VISIBLE_BROWSING_START = 'VisibleBrowsingStart';
    public const ELEMENT_VISIBLE_FRONTDOOR      = 'VisibleFrontdoor';
    public const ELEMENT_VISIBLE_OAI            = 'VisibleOai';
    public const ELEMENT_DISPLAY_BROWSING       = 'DisplayBrowsing';
    public const ELEMENT_DISPLAY_FRONTDOOR      = 'DisplayFrontdoor';
    public const ELEMENT_ASSIGN_ROOT            = 'AssignRoot';
    public const ELEMENT_ASSIGN_LEAVES_ONLY     = 'AssignLeavesOnly';
    public const ELEMENT_HIDE_EMPTY_COLLECTIONS = 'HideEmptyCollections';

    public function init()
    {
        parent::init();

        $this->setRemoveEmptyCheckbox(false);
        $this->setUseNameAsLabel(true);

        $fieldName = CollectionRole::describeField(CollectionRole::FIELD_NAME);

        $this->addElement('text', self::ELEMENT_NAME, [
            'required'  => true,
            'size'      => 70,
            'maxlength' => $fieldName->getMaxSize(),
        ]);
        $this->getElement(self::ELEMENT_NAME)->addValidators([
            new Application_Form_Validate_CollectionRoleNameUnique(),
            new Application_Form_Validate_CollectionRoleName(),
        ]);

        $this->addElement('translation', self::ELEMENT_DISPLAYNAME, [
            'required' => false,
            'size'     => 70,
        ]);

        $fieldOaiName = CollectionRole::describeField(CollectionRole::FIELD_OAI_NAME);

        $this->addElement('text', self::ELEMENT_OAI_NAME, [
            'required'  => true,
            'size'      => 30,
            'maxlength' => $fieldOaiName->getMaxSize(),
        ]);
        $this->getElement(self::ELEMENT_OAI_NAME)->addValidator(
            new Application_Form_Validate_CollectionRoleOaiNameUnique()
        );

        $this->addElement('Position', self::ELEMENT_POSITION);
        $this->addElement('checkbox', self::ELEMENT_VISIBLE);
        $this->addElement('checkbox', self::ELEMENT_VISIBLE_BROWSING_START);
        $this->addElement('checkbox', self::ELEMENT_VISIBLE_FRONTDOOR);
        $this->addElement('checkbox', self::ELEMENT_VISIBLE_OAI);
        $this->addElement('CollectionDisplayFormat', self::ELEMENT_DISPLAY_BROWSING, ['required' => true]);
        $this->addElement('CollectionDisplayFormat', self::ELEMENT_DISPLAY_FRONTDOOR, ['required' => true]);
        $this->addElement('checkbox', self::ELEMENT_ASSIGN_ROOT);
        $this->addElement('checkbox', self::ELEMENT_ASSIGN_LEAVES_ONLY);
        $this->addElement('checkbox', self::ELEMENT_HIDE_EMPTY_COLLECTIONS);

        $this->removeElement('Cancel');
    }

    /**
     * @param CollectionRoleInterface $collectionRole
     */
    public function populateFromModel($collectionRole)
    {
        $name = $collectionRole->getName();
        $this->getElement(self::ELEMENT_DISPLAYNAME)->populateFromTranslations(
            'default_collection_role_' . $name
        );

        $this->getElement(self::ELEMENT_MODEL_ID)->setValue($collectionRole->getId());
        $this->getElement(self::ELEMENT_NAME)->setValue($name);
        $this->getElement(self::ELEMENT_OAI_NAME)->setValue($collectionRole->getOaiName());
        $this->getElement(self::ELEMENT_POSITION)->setValue($collectionRole->getPosition());
        $this->getElement(self::ELEMENT_VISIBLE)->setValue($collectionRole->getVisible());
        $this->getElement(self::ELEMENT_VISIBLE_OAI)->setValue($collectionRole->getVisibleOai());
        $this->getElement(self::ELEMENT_VISIBLE_BROWSING_START)->setValue($collectionRole->getVisibleBrowsingStart());
        $this->getElement(self::ELEMENT_VISIBLE_FRONTDOOR)->setValue($collectionRole->getVisibleFrontdoor());
        $this->getElement(self::ELEMENT_DISPLAY_BROWSING)->setValue($collectionRole->getDisplayBrowsing());
        $this->getElement(self::ELEMENT_DISPLAY_FRONTDOOR)->setValue($collectionRole->getDisplayFrontdoor());
        $this->getElement(self::ELEMENT_ASSIGN_ROOT)->setValue($collectionRole->getAssignRoot());
        $this->getElement(self::ELEMENT_ASSIGN_LEAVES_ONLY)->setValue($collectionRole->getAssignLeavesOnly());
        $this->getElement(self::ELEMENT_HIDE_EMPTY_COLLECTIONS)->setValue($collectionRole->getHideEmptyCollections());
    }

    /**
     * @param CollectionRoleInterface $collectionRole
     */
    public function updateModel($collectionRole)
    {
        $collectionRole->setName($this->getElementValue(self::ELEMENT_NAME));
        $collectionRole->setOaiName($this->getElementValue(self::ELEMENT_OAI_NAME));
        $collectionRole->setPosition($this->getElementValue(self::ELEMENT_POSITION));
        $collectionRole->setVisible($this->getElementValue(self::ELEMENT_VISIBLE));
        $collectionRole->setVisibleBrowsingStart($this->getElementValue(self::ELEMENT_VISIBLE_BROWSING_START));
        $collectionRole->setVisibleFrontdoor($this->getElementValue(self::ELEMENT_VISIBLE_FRONTDOOR));
        $collectionRole->setVisibleOai($this->getElementValue(self::ELEMENT_VISIBLE_OAI));
        $collectionRole->setDisplayBrowsing($this->getElementValue(self::ELEMENT_DISPLAY_BROWSING));
        $collectionRole->setDisplayFrontdoor($this->getElementValue(self::ELEMENT_DISPLAY_FRONTDOOR));
        $collectionRole->setAssignRoot($this->getElementValue(self::ELEMENT_ASSIGN_ROOT));
        $collectionRole->setAssignLeavesOnly($this->getElementValue(self::ELEMENT_ASSIGN_LEAVES_ONLY));
        $collectionRole->setHideEmptyCollections($this->getElementValue(self::ELEMENT_HIDE_EMPTY_COLLECTIONS));
    }
}
