<?php
/*
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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Document;

/**
 * Unterformular fuer weitere Metadaten eines Dokuments.
 *
 * @category    Application
 * @package     Module_Admin
 * @subpackage  Form_Document
 */
class Admin_Form_Document_Bibliographic extends Admin_Form_Document_Section
{

    const ELEMENT_CONTRIBUTING_CORPORATION = 'ContributingCorporation';
    const ELEMENT_CREATING_CORPORATION = 'CreatingCorporation';
    const ELEMENT_EDITION = 'Edition';
    const ELEMENT_ISSUE = 'Issue';
    const ELEMENT_ARTICLE_NUMBER = 'ArticleNumber';
    const ELEMENT_PAGE_FIRST = 'PageFirst';
    const ELEMENT_PAGE_LAST = 'PageLast';
    const ELEMENT_PAGE_COUNT = 'PageCount';
    const ELEMENT_PUBLISHER_NAME = 'PublisherName';
    const ELEMENT_PUBLISHER_PLACE = 'PublisherPlace';
    const ELEMENT_VOLUME = 'Volume';
    const ELEMENT_THESIS_DATE_ACCEPTED = 'ThesisDateAccepted';
    const ELEMENT_THESIS_YEAR_ACCEPTED = 'ThesisYearAccepted';
    const ELEMENT_BELONGS_TO_BIBLIOGRAPHY = 'BelongsToBibliography';

    public function init()
    {
        parent::init();

        $this->setLegend('admin_document_section_bibliographic');

        $this->setUseNameAsLabel(true);

        // Label entsprechen den Namen der Elemente
        $this->addElement('text', self::ELEMENT_EDITION, ['size' => 70]);
        $this->addElement('text', self::ELEMENT_VOLUME, ['size' => 30]);
        $this->addElement('text', self::ELEMENT_PUBLISHER_NAME, ['size' => 70]);
        $this->addElement('text', self::ELEMENT_PUBLISHER_PLACE, ['size' => 70]);

        $this->addElement('text', self::ELEMENT_PAGE_COUNT, ['size' => 15]);
        $this->addElement('text', self::ELEMENT_PAGE_FIRST, ['size' => 15]);
        $this->addElement('text', self::ELEMENT_PAGE_LAST, ['size' => 15]);

        $this->addElement('text', self::ELEMENT_ISSUE, ['size' => 30]);
        $this->addElement('text', self::ELEMENT_ARTICLE_NUMBER, ['size' => 15]);

        $this->addElement('text', self::ELEMENT_CONTRIBUTING_CORPORATION, ['size' => 70]);
        $this->addElement('text', self::ELEMENT_CREATING_CORPORATION, ['size' => 70]);

        $this->addElement('Date', self::ELEMENT_THESIS_DATE_ACCEPTED);
        $this->addElement('Year', self::ELEMENT_THESIS_YEAR_ACCEPTED);

        $this->addSubForm(
            new Admin_Form_Document_MultiSubForm(
                'Admin_Form_Document_Publisher',
                'ThesisPublisher',
                new Application_Form_Validate_MultiSubForm_RepeatedValues(
                    Admin_Form_Document_Institute::ELEMENT_INSTITUTE,
                    'admin_document_error_repeated_institute'
                )
            ),
            'Publishers'
        );
        $this->addSubForm(
            new Admin_Form_Document_MultiSubForm(
                'Admin_Form_Document_Grantor',
                'ThesisGrantor',
                new Application_Form_Validate_MultiSubForm_RepeatedValues(
                    Admin_Form_Document_Institute::ELEMENT_INSTITUTE,
                    'admin_document_error_repeated_institute'
                )
            ),
            'Grantors'
        );

        $this->addElement('checkbox', self::ELEMENT_BELONGS_TO_BIBLIOGRAPHY);

        $this->setRemoveEmptyCheckbox(false);
    }

    /**
     * @param Document $document
     */
    public function populateFromModel($document)
    {
        parent::populateFromModel($document);

        $datesHelper = \Zend_Controller_Action_HelperBroker::getStaticHelper('Dates');

        $this->getElement(self::ELEMENT_CONTRIBUTING_CORPORATION)->setValue($document->getContributingCorporation());
        $this->getElement(self::ELEMENT_CREATING_CORPORATION)->setValue($document->getCreatingCorporation());
        $this->getElement(self::ELEMENT_EDITION)->setValue($document->getEdition());
        $this->getElement(self::ELEMENT_ISSUE)->setValue($document->getIssue());
        $this->getElement(self::ELEMENT_ARTICLE_NUMBER)->setValue($document->getArticleNumber());
        $this->getElement(self::ELEMENT_PAGE_FIRST)->setValue($document->getPageFirst());
        $this->getElement(self::ELEMENT_PAGE_LAST)->setValue($document->getPageLast());
        $this->getElement(self::ELEMENT_PAGE_COUNT)->setValue($document->getPageNumber());
        $this->getElement(self::ELEMENT_PUBLISHER_NAME)->setValue($document->getPublisherName());
        $this->getElement(self::ELEMENT_PUBLISHER_PLACE)->setValue($document->getPublisherPlace());
        $this->getElement(self::ELEMENT_VOLUME)->setValue($document->getVolume());

        $date = $datesHelper->getDateString($document->getThesisDateAccepted());
        $this->getElement(self::ELEMENT_THESIS_DATE_ACCEPTED)->setValue($date);
        $this->getElement(self::ELEMENT_THESIS_YEAR_ACCEPTED)->setValue($document->getThesisYearAccepted());
        $this->getElement(self::ELEMENT_BELONGS_TO_BIBLIOGRAPHY)->setValue($document->getBelongsToBibliography());
    }

    /**
     * @param Document $document
     */
    public function updateModel($document)
    {
        parent::updateModel($document);

        $datesHelper = \Zend_Controller_Action_HelperBroker::getStaticHelper('Dates');

        $document->setContributingCorporation($this->getElementValue(self::ELEMENT_CONTRIBUTING_CORPORATION));
        $document->setCreatingCorporation($this->getElementValue(self::ELEMENT_CREATING_CORPORATION));
        $document->setEdition($this->getElementValue(self::ELEMENT_EDITION));
        $document->setIssue($this->getElementValue(self::ELEMENT_ISSUE));
        $document->setArticleNumber($this->getElementValue(self::ELEMENT_ARTICLE_NUMBER));
        $document->setPageFirst($this->getElementValue(self::ELEMENT_PAGE_FIRST));
        $document->setPageLast($this->getElementValue(self::ELEMENT_PAGE_LAST));
        $document->setPageNumber($this->getElementValue(self::ELEMENT_PAGE_COUNT));
        $document->setPublisherName($this->getElementValue(self::ELEMENT_PUBLISHER_NAME));
        $document->setPublisherPlace($this->getElementValue(self::ELEMENT_PUBLISHER_PLACE));
        $document->setVolume($this->getElementValue(self::ELEMENT_VOLUME));

        $value = $this->getElementValue(self::ELEMENT_THESIS_DATE_ACCEPTED);
        $date = (is_null($value)) ? null : $datesHelper->getOpusDate($value);
        $document->setThesisDateAccepted($date);

        $document->setThesisYearAccepted($this->getElementValue(self::ELEMENT_THESIS_YEAR_ACCEPTED));
        $document->setBelongsToBibliography($this->getElementValue(self::ELEMENT_BELONGS_TO_BIBLIOGRAPHY));
    }
}
