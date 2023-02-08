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

use Opus\Common\Series;
use Opus\Common\SeriesInterface;

class Admin_Form_Series extends Application_Form_Model_Abstract
{
    public const ELEMENT_TITLE      = 'Title';
    public const ELEMENT_INFOBOX    = 'Infobox';
    public const ELEMENT_VISIBLE    = 'Visible';
    public const ELEMENT_SORT_ORDER = 'SortOrder';

    public function init()
    {
        parent::init();

        $this->setRemoveEmptyCheckbox(false);
        $this->setUseNameAsLabel(true);
        $this->setModelClass(Series::class);

        $this->addElement('text', self::ELEMENT_TITLE, ['required' => true, 'size' => 70]);
        $this->addElement('textarea', self::ELEMENT_INFOBOX);
        $this->addElement('checkbox', self::ELEMENT_VISIBLE);
        $this->addElement('text', self::ELEMENT_SORT_ORDER, ['required' => true]); // TODO improve?
    }

    /**
     * @param SeriesInterface $series
     */
    public function populateFromModel($series)
    {
        $this->getElement(self::ELEMENT_MODEL_ID)->setValue($series->getId());
        $this->getElement(self::ELEMENT_TITLE)->setValue($series->getTitle());
        $this->getElement(self::ELEMENT_INFOBOX)->setValue($series->getInfobox());
        $this->getElement(self::ELEMENT_VISIBLE)->setValue($series->getVisible());
        $this->getElement(self::ELEMENT_SORT_ORDER)->setValue($series->getSortOrder());
    }

    /**
     * @param SeriesInterface $series
     */
    public function updateModel($series)
    {
        $series->setTitle($this->getElementValue(self::ELEMENT_TITLE));
        $series->setInfobox($this->getElementValue(self::ELEMENT_INFOBOX));
        $series->setVisible($this->getElementValue(self::ELEMENT_VISIBLE));
        $series->setSortOrder($this->getElementValue(self::ELEMENT_SORT_ORDER));
    }
}
