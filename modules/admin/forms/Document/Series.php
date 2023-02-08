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
use Opus\Common\Series;
use Opus\Model\Dependent\Link\DocumentSeries;

/**
 * Unterformular fuer das Editieren eines Serieneintrags.
 *
 * TODO gibt es gute Lösung die Doc-ID nicht noch einmal im Unterformular zu haben (als Teil der ID)
 */
class Admin_Form_Document_Series extends Admin_Form_AbstractModelSubForm
{
    /**
     * Name von Formelement für Dokument-ID (Teil des Schlüssels für Link DocumentSeries).
     */
    public const ELEMENT_DOC_ID = 'Id';

    /**
     * Name von Formelement für Series-ID.
     */
    public const ELEMENT_SERIES_ID = 'SeriesId';

    /**
     * Name von Formelement für Label/Nummer des Dokuments in Schriftenreihe.
     */
    public const ELEMENT_NUMBER = 'Number';

    /**
     * Name von Formelement für die Sortierposition in Schriftenreihe.
     */
    public const ELEMENT_SORT_ORDER = 'SortOrder';

    /**
     * Erzeugt die Formulareelemente.
     */
    public function init()
    {
        parent::init();

        // Schluessel fuer Link Objekte ist Dokument-ID + Series-ID
        $this->addElement('Hidden', self::ELEMENT_DOC_ID);

        $this->addElement('Series', self::ELEMENT_SERIES_ID);
        $number = $this->createElement('text', self::ELEMENT_NUMBER, ['required' => true]);
        // $number->addValidator(new Application_Form_Validate_SeriesNumberAvailable());
        $this->addElement($number);
        $this->addElement('SortOrder', self::ELEMENT_SORT_ORDER);
    }

    /**
     * Initialisiert das Formular mit den Werten im Modell.
     *
     * @param DocumentSeries $seriesLink
     */
    public function populateFromModel($seriesLink)
    {
        $linkId = $seriesLink->getId();
        $this->getElement(self::ELEMENT_DOC_ID)->setValue($linkId[0]);
        $series = $seriesLink->getModel();
        $this->getElement(self::ELEMENT_SERIES_ID)->setValue($series->getId());
        $this->getElement(self::ELEMENT_NUMBER)->setValue($seriesLink->getNumber());
        $this->getElement(self::ELEMENT_SORT_ORDER)->setValue($seriesLink->getDocSortOrder());
    }

    /**
     * Aktualisiert das Modell mit den Werten im Formular.
     *
     * @param type $seriesLink
     */
    public function updateModel($seriesLink)
    {
        $seriesId = $this->getElementValue(self::ELEMENT_SERIES_ID);
        $series   = Series::get($seriesId);
        $seriesLink->setModel($series);
        $seriesLink->setNumber($this->getElementValue(self::ELEMENT_NUMBER));
        $seriesLink->setDocSortOrder($this->getElementValue(self::ELEMENT_SORT_ORDER));
    }

    /**
     * Liefert das angezeigte Modell oder ein neues für hinzugefügte Verknüpfungen.
     *
     * @return DocumentSeries
     */
    public function getModel()
    {
        $docId = $this->getElement(self::ELEMENT_DOC_ID)->getValue();

        if (empty($docId)) {
            $linkId = null;
        } else {
            $seriesId = $this->getElement(self::ELEMENT_SERIES_ID)->getValue();
            $linkId   = [$docId, $seriesId];
        }

        try {
            $seriesLink = new DocumentSeries($linkId);
        } catch (NotFoundException $omnfe) {
            $seriesLink = new DocumentSeries();
        }

        $this->updateModel($seriesLink);

        return $seriesLink;
    }
}
