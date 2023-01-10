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

use Opus\Common\DocumentInterface;

/**
 * Formular fuer allgemeine Felder von Document.
 *
 * TODO validierung
 */
class Admin_Form_Document_General extends Admin_Form_AbstractDocumentSubForm
{
    /**
     * Name des Formularelements fuer die Sprache des Dokuments.
     */
    public const ELEMENT_LANGUAGE = 'Language';

    /**
     * Name des Formularelements fuer den Dokumententyp.
     */
    public const ELEMENT_TYPE = 'Type';

    /**
     * Name des Formularelements fuer das Feld PublishedDate.
     */
    public const ELEMENT_PUBLISHED_DATE = 'PublishedDate';

    /**
     * Name des Formularelements fuer das Feld PublishedYear.
     */
    public const ELEMENT_PUBLISHED_YEAR = 'PublishedYear';

    /**
     * Name des Formularelements fuer das Feld CompletedDate.
     */
    public const ELEMENT_COMPLETED_DATE = 'CompletedDate';

    /**
     * Name des Formularelements fuer das Feld CompletedYear.
     */
    public const ELEMENT_COMPLETED_YEAR = 'CompletedYear';

    /**
     * Name des Formularelements fuer das Feld EmbargoDate.
     */
    public const ELEMENT_EMBARGO_DATE = 'EmbargoDate';

    /**
     * Erzeugt die Formularelemente.
     */
    public function init()
    {
        parent::init();

        $this->setLegend('admin_document_section_general');

        $this->addElement('Language', self::ELEMENT_LANGUAGE, ['label' => 'Language', 'required' => true]);
        $this->addElement('DocumentType', self::ELEMENT_TYPE, ['required' => 'true']);

        $this->addElement('Date', self::ELEMENT_PUBLISHED_DATE);
        $this->addElement('Year', self::ELEMENT_PUBLISHED_YEAR);

        $this->addElement('Date', self::ELEMENT_COMPLETED_DATE);
        $this->addElement('Year', self::ELEMENT_COMPLETED_YEAR);

        $this->addElement('Date', self::ELEMENT_EMBARGO_DATE);
    }

    /**
     * Befuellt das Formular anhand der Metadaten eines Dokuments.
     *
     * @param DocumentInterface $document
     */
    public function populateFromModel($document)
    {
        $datesHelper = $this->getDatesHelper();

        $this->getElement(self::ELEMENT_LANGUAGE)->setValue($document->getLanguage());
        $this->getElement(self::ELEMENT_TYPE)->setValue($document->getType());

        $date = $datesHelper->getDateString($document->getCompletedDate());
        $this->getElement(self::ELEMENT_COMPLETED_DATE)->setValue($date);
        $this->getElement(self::ELEMENT_COMPLETED_YEAR)->setValue($document->getCompletedYear());

        $date = $datesHelper->getDateString($document->getPublishedDate());
        $this->getElement(self::ELEMENT_PUBLISHED_DATE)->setValue($date);
        $this->getElement(self::ELEMENT_PUBLISHED_YEAR)->setValue($document->getPublishedYear());

        $date = $datesHelper->getDateString($document->getEmbargoDate());
        $this->getElement(self::ELEMENT_EMBARGO_DATE)->setValue($date);
    }

    /**
     * Aktualisiert ein Dokument mit den Werten im Formular.
     *
     * @param DocumentInterface $document
     */
    public function updateModel($document)
    {
        // Language
        $value = $this->getElementValue(self::ELEMENT_LANGUAGE);
        $document->setLanguage($value);

        // Type
        $value = $this->getElementValue(self::ELEMENT_TYPE);
        $document->setType($value);

        $datesHelper = $this->getDatesHelper();

        // CompletedDate
        $value = $this->getElementValue(self::ELEMENT_COMPLETED_DATE);
        $date  = $datesHelper->getOpusDate($value);
        $document->setCompletedDate($date);

        // CompletedYear
        $value = $this->getElementValue(self::ELEMENT_COMPLETED_YEAR);
        $document->setCompletedYear($value);

        // PublishedDate
        $value = $this->getElementValue(self::ELEMENT_PUBLISHED_DATE);
        $date  = $datesHelper->getOpusDate($value);
        $document->setPublishedDate($date);

        // PublishedYear
        $value = $this->getElementValue(self::ELEMENT_PUBLISHED_YEAR);
        $document->setPublishedYear($value);

        $value = $this->getElementValue(self::ELEMENT_EMBARGO_DATE);
        $date  = $datesHelper->getOpusDate($value);
        $document->setEmbargoDate($date);
    }
}
