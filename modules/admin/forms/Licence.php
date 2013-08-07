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
 */

/**
 * Klasse für Lizenz-Formular.
 *
 * @category    Application
 * @package     Admin_Form
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 *
 * TODO rendering of Active und PodAllowed mit Wert (Ja/Nein)
 */
class Admin_Form_Licence extends Application_Form_Model_Abstract {

    const ELEMENT_ACTIVE = 'Active';

    const ELEMENT_COMMENT_INTERNAL = 'CommentInternal';

    const ELEMENT_DESC_MARKUP = 'DescMarkup';

    const ELEMENT_DESC_TEXT = 'DescText';

    const ELEMENT_LANGUAGE = 'Language';

    const ELEMENT_LINK_LICENCE = 'LinkLicence';

    const ELEMENT_LINK_LOGO = 'LinkLogo';

    const ELEMENT_LINK_SIGN = 'LinkSign';

    const ELEMENT_MIME_TYPE = 'MimeType';

    const ELEMENT_NAME_LONG = 'NameLong';

    const ELEMENT_SORT_ORDER = 'SortOrder';

    const ELEMENT_POD_ALLOWED = 'PodAllowed';

    public function init() {
        parent::init();

        $this->setRemoveEmptyCheckbox(false);
        $this->setUseNameAsLabel(true);
        $this->setModelClass('Opus_Licence');

        $this->addElement('checkbox', self::ELEMENT_ACTIVE);
        $this->addElement('text', self::ELEMENT_NAME_LONG, array('required' => true));
        $this->addElement('Language', self::ELEMENT_LANGUAGE, array('required' => true));
        $this->addElement('text', self::ELEMENT_LINK_LICENCE, array('required' => true));
        $this->addElement('text', self::ELEMENT_LINK_LOGO);
        $this->addElement('text', self::ELEMENT_LINK_SIGN);
        $this->addElement('textarea', self::ELEMENT_DESC_TEXT);
        $this->addElement('textarea', self::ELEMENT_DESC_MARKUP);
        $this->addElement('textarea', self::ELEMENT_COMMENT_INTERNAL);
        $this->addElement('text', self::ELEMENT_MIME_TYPE);
        $this->addElement('checkbox', self::ELEMENT_POD_ALLOWED);
        $this->addElement('SortOrder', self::ELEMENT_SORT_ORDER);
    }

    public function populateFromModel($licence) {
        $this->getElement(self::ELEMENT_MODEL_ID)->setValue($licence->getId());
        $this->getElement(self::ELEMENT_ACTIVE)->setValue($licence->getActive());
        $this->getElement(self::ELEMENT_COMMENT_INTERNAL)->setValue($licence->getCommentInternal());
        $this->getElement(self::ELEMENT_DESC_MARKUP)->setValue($licence->getDescMarkup());
        $this->getElement(self::ELEMENT_DESC_TEXT)->setValue($licence->getDescText());
        $this->getElement(self::ELEMENT_LANGUAGE)->setValue($licence->getLanguage());
        $this->getElement(self::ELEMENT_LINK_LICENCE)->setValue($licence->getLinkLicence());
        $this->getElement(self::ELEMENT_LINK_LOGO)->setValue($licence->getLinkLogo());
        $this->getElement(self::ELEMENT_LINK_SIGN)->setValue($licence->getLinkSign());
        $this->getElement(self::ELEMENT_MIME_TYPE)->setValue($licence->getMimeType());
        $this->getElement(self::ELEMENT_NAME_LONG)->setValue($licence->getNameLong());
        $this->getElement(self::ELEMENT_SORT_ORDER)->setValue($licence->getSortOrder());
        $this->getElement(self::ELEMENT_POD_ALLOWED)->setValue($licence->getPodAllowed());
    }

    public function updateModel($licence) {
        $licence->setActive($this->getElementValue(self::ELEMENT_ACTIVE));
        $licence->setCommentInternal($this->getElementValue(self::ELEMENT_COMMENT_INTERNAL));
        $licence->setDescMarkup($this->getElementValue(self::ELEMENT_DESC_MARKUP));
        $licence->setDescText($this->getElementValue(self::ELEMENT_DESC_TEXT));
        $licence->setLanguage($this->getElementValue(self::ELEMENT_LANGUAGE));
        $licence->setLinkLicence($this->getElementValue(self::ELEMENT_LINK_LICENCE));
        $licence->setLinkLogo($this->getElementValue(self::ELEMENT_LINK_LOGO));
        $licence->setLinkSign($this->getElementValue(self::ELEMENT_LINK_SIGN));
        $licence->setMimeType($this->getElementValue(self::ELEMENT_MIME_TYPE));
        $licence->setNameLong($this->getElementValue(self::ELEMENT_NAME_LONG));
        $licence->setSortOrder($this->getElementValue(self::ELEMENT_SORT_ORDER));
        $licence->setPodAllowed($this->getElementValue(self::ELEMENT_POD_ALLOWED));
    }

}