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

use Opus\Common\Iprange;
use Opus\Common\IprangeInterface;

/**
 * Form for creating or editing IP ranges.
 */
class Admin_Form_IpRange extends Application_Form_Model_Abstract
{
    public const ELEMENT_NAME = 'Name';

    public const ELEMENT_STARTING_IP = 'Startingip';

    public const ELEMENT_ENDING_IP = 'Endingip';

    public const ELEMENT_ROLES = 'Roles';

    /**
     * Pattern for valid names of ip ranges.
     */
    public const NAME_PATTERN = '/^[a-z]/i';

    /**
     * Initializes form and adds display group for roles.
     */
    public function init()
    {
        parent::init();

        $this->setUseNameAsLabel(true);
        $this->setLabelPrefix('admin_iprange_label_');
        $this->setModelClass(Iprange::class);

        $name = $this->createElement('text', self::ELEMENT_NAME, ['required' => true]);
        $name->addValidator('regex', false, [
            'pattern'  => self::NAME_PATTERN,
            'messages' => [
                'regexNotMatch' => 'validation_error_iprange_name_regexNotMatch',
            ],
        ]);
        $name->addValidator('stringLength', false, [
            'min'      => 3,
            'max'      => 20,
            'messages' => [
                'stringLengthTooShort' => 'validation_error_stringLengthTooShort',
                'stringLengthTooLong'  => 'validation_error_stringLengthTooLong',
            ],
        ]);
        $this->addElement($name);

        $this->addElement('ipAddress', self::ELEMENT_STARTING_IP, ['required' => true]);
        $this->addElement('ipAddress', self::ELEMENT_ENDING_IP);

        $roles = $this->createElement('roles', self::ELEMENT_ROLES);
        $roles->setAllowEmpty(true);
        $this->addElement($roles);
    }

    /**
     * Populates form with values from Iprange instance.
     *
     * @param IprangeInterface $ipRange
     */
    public function populateFromModel($ipRange)
    {
        $this->getElement(self::ELEMENT_MODEL_ID)->setValue($ipRange->getId());
        $this->getElement(self::ELEMENT_NAME)->setValue($ipRange->getName());
        $this->getElement(self::ELEMENT_STARTING_IP)->setValue($ipRange->getStartingIp());
        $this->getElement(self::ELEMENT_ENDING_IP)->setValue($ipRange->getEndingIp());
        $this->getElement(self::ELEMENT_ROLES)->setValue($ipRange->getRole());
    }

    /**
     * Updates object with values from form elements.
     *
     * @param IprangeInterface $ipRange
     */
    public function updateModel($ipRange)
    {
        $ipRange->setName($this->getElementValue(self::ELEMENT_NAME));

        $startingIp = $this->getElementValue(self::ELEMENT_STARTING_IP);
        $endingIp   = $this->getElementValue(self::ELEMENT_ENDING_IP);

        // starting and ending ip must be set in database
        if (empty($endingIp)) {
            $endingIp = $startingIp;
        }

        $ipRange->setStartingIp($startingIp);
        $ipRange->setEndingIp($endingIp);

        $ipRange->setRole($this->getElement(self::ELEMENT_ROLES)->getRoles());
    }
}
