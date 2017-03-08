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
 * @package     Module_Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Form for creating or editing IP ranges.
 */
class Admin_Form_IpRange extends Application_Form_Model_Abstract
{

    const ELEMENT_NAME = 'Name';

    const ELEMENT_STARTING_IP = 'Startingip';

    const ELEMENT_ENDING_IP = 'Endingip';

    const ELEMENT_ROLES = 'Roles';

    /**
     * Pattern for valid names of ip ranges.
     */
    const NAME_PATTERN = '/^[a-z]/i';

    /**
     * Initializes form and adds display group for roles.
     */
    public function init() {
        parent::init();

        $this->setUseNameAsLabel(true);
        $this->setLabelPrefix('admin_iprange_label_');
        $this->setModelClass('Opus_Iprange');

        $name = $this->createElement('text', self::ELEMENT_NAME, array('required' => true));
        $name->addValidator('regex', false, array('pattern' => self::NAME_PATTERN, 'messages' => array(
            'regexNotMatch' => 'validation_error_iprange_name_regexNotMatch'
        )));
        $name->addValidator('stringLength', false, array('min' => 3, 'max' => 20, 'messages' => array(
            'stringLengthTooShort' => 'validation_error_stringLengthTooShort',
            'stringLengthTooLong' => 'validation_error_stringLengthTooLong'
        )));
        $this->addElement($name);

        $this->addElement('ipAddress', self::ELEMENT_STARTING_IP, array('required' => true));
        $this->addElement('ipAddress', self::ELEMENT_ENDING_IP);

        $roles = $this->createElement('roles', self::ELEMENT_ROLES);
        $roles->setAllowEmpty(true);
        $this->addElement($roles);
    }

    /**
     * Populates form with values from Opus_Iprange instance.
     * @param Opus_Iprange $ipRange
     */
    public function populateFromModel($ipRange) {
        $this->getElement(self::ELEMENT_MODEL_ID)->setValue($ipRange->getId());
        $this->getElement(self::ELEMENT_NAME)->setValue($ipRange->getName());
        $this->getElement(self::ELEMENT_STARTING_IP)->setValue($ipRange->getStartingip());
        $this->getElement(self::ELEMENT_ENDING_IP)->setValue($ipRange->getEndingip());
        $this->getElement(self::ELEMENT_ROLES)->setValue($ipRange->getRole());
    }

    /**
     * Updates object with values from form elements.
     * @param $ipRange Opus_IpRange
     */
    public function updateModel($ipRange) {
        $ipRange->setName($this->getElementValue(self::ELEMENT_NAME));

        $startingIp = $this->getElementValue(self::ELEMENT_STARTING_IP);
        $endingIp = $this->getElementValue(self::ELEMENT_ENDING_IP);

        // starting and ending ip must be set in database
        if (empty($endingIp))
        {
            $endingIp = $startingIp;
        }

        $ipRange->setStartingip($startingIp);
        $ipRange->setEndingip($endingIp);

        $ipRange->setRole($this->getElement(self::ELEMENT_ROLES)->getRoles());
    }

}
