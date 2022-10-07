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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Model\NotFoundException;
use Opus\Common\DnbInstitute;
use Opus\Model\Dependent\Link\DocumentDnbInstitute;
use Opus\Model\Dependent\Link\AbstractLinkModel;

/**
 * Unterformular fuer Institute.
 *
 * @category    Application
 * @package     Module_Admin
 * @subpackage  Form_Document
 */
class Admin_Form_Document_Institute extends Admin_Form_AbstractModelSubForm
{

    const ROLE_PUBLISHER = 'publisher';

    const ROLE_GRANTOR = 'grantor';

    const ELEMENT_DOC_ID = 'Id';

    const ELEMENT_INSTITUTE = 'Institute';

    /**
     * @var string ROLE_GRANTOR or ROLE_PUBLISHER
     */
    private $_role;

    public function __construct($role, $options = null)
    {
        $this->_role = $role;
        parent::__construct($options);
    }

    public function init()
    {
        parent::init();

        $this->addElement('Hidden', self::ELEMENT_DOC_ID);

        switch ($this->_role) {
            case self::ROLE_PUBLISHER:
                $this->addElement('Publisher', self::ELEMENT_INSTITUTE);
                break;
            case self::ROLE_GRANTOR:
                $this->addElement('Grantor', self::ELEMENT_INSTITUTE);
                break;
            default:
                throw new Application_Exception(__METHOD__ . ' Unknown role \'' . $this->_role . '\'.');
        }
    }

    public function populateFromModel($link)
    {
        $linkId = $link->getId();
        $this->getElement(self::ELEMENT_DOC_ID)->setValue($linkId[0]);
        $this->getElement(self::ELEMENT_INSTITUTE)->setValue($link->getModel()->getId());
    }

    /**
     * @param $model AbstractLinkModel
     */
    public function updateModel($link)
    {
        $instituteId = $this->getElement(self::ELEMENT_INSTITUTE)->getValue();
        try {
            $institute = DnbInstitute::get($instituteId);

            $link->setModel($institute);
        } catch (NotFoundException $omnfe) {
            $this->getLogger()->err(__METHOD__ . " Unknown institute ID = '$instituteId'.");
        }
    }

    public function getModel()
    {
        $docId = $this->getElement(self::ELEMENT_DOC_ID)->getValue();

        if (empty($docId)) {
            $linkId = null;
        } else {
            $instituteId = $this->getElement(self::ELEMENT_INSTITUTE)->getValue();
            $linkId = [$docId, $instituteId, $this->_role];
        }

        try {
            $link = new DocumentDnbInstitute($linkId);
        } catch (NotFoundException $omnfe) {
            $link = new DocumentDnbInstitute();
        }

        $this->updateModel($link);

        return $link;
    }

    public function loadDefaultDecorators()
    {
        parent::loadDefaultDecorators();

        $this->removeDecorator('Fieldset');
    }
}
