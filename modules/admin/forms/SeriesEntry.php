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
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Form for adding or editing assignments between a document and a series.
 *
 * TODO validate duplicate series IDs in parent form
 */
class Admin_Form_SeriesEntry extends Zend_Form_SubForm {

    const MODEL_CLASS = 'Opus_Model_Dependent_Link_DocumentSeries';

    private $document;

    /**
     * Constructs form.
     */
    public function __construct($document = null) {
        parent::__construct();
        $this->document = $document;
        $this->__init();
    }

    /**
     * Constructs form elements.
     */
    private function __init() {
        $series = $this->__getSeriesSelect();
        $series->setRequired(true);
        // TODO validate if series exists
        // TODO validate if series is already assigned (for add)

        $number = new Zend_Form_Element_Text('Number');
        $number->setLabel(self::MODEL_CLASS . '_Number')
                ->setRequired(true)
                ->addValidator(new Zend_Validate_Alnum());

        $sortOrder = new Zend_Form_Element_Text('SortOrder');
        $sortOrder->setLabel(self::MODEL_CLASS . '_SortOrder')
                ->setRequired(true)
                ->addValidator(new Zend_Validate_Int())
                ->setValue(0);

        $this->addElement($series);
        $this->addElement($number);
        $this->addElement($sortOrder);
    }

    /**
     * Generate select box for series.
     * @return Zend_Form_Element_Select Form element for selecting a series
     */
    private function __getSeriesSelect() {
        $select = new Zend_Form_Element_Select('Series');

        foreach ($this->getNotAssignedSeries($this->document) as $index => $series) {
            $select->addMultiOption($series->getId(), $series->getTitle());
        }

        $select->setLabel(self::MODEL_CLASS . '_Series');

        return $select;
    }

    /**
     * Populates form from a value of field 'Series' of Opus_Document.
     * @param Opus_Model_Dependent_Link_DocumentSeries $model
     */
    public function populateFromModel($model) {
        if (!($model instanceof Opus_Model_Dependent_Link_DocumentSeries)) {
            throw new Application_Exception('Opus_Model_Dependent_Link_DocumentSeries expected!');
        }

        $this->getElement('Series')->addMultiOption($model->getModel()->getId(), $model->getTitle());
        $this->getElement('Series')->setValue($model->getModel()->getId());
        $this->getElement('Number')->setValue($model->getNumber());
        $this->getElement('SortOrder')->setValue($model->getSortOrder());
    }

    /**
     * Populates form from POST data.
     * @param array $post
     */
    public function populateFromPost($post) {
        $this->getElement('Series')->setValue($post['Series']);
        $this->getElement('Number')->setValue($post['Number']);
        $this->getElement('SortOrder')->setValue($post['SortOrder']);
    }

    /**
     * Returns list of series that are not assigned to document.
     * @param Opus_Document Document for filtering list
     * @return array Opus_Series
     */
    private function getNotAssignedSeries($document) {
        $seriesAll = Opus_Series::getAll();

        $assignedSeries = array();

        if (!is_null($document)) {
            foreach ($document->getSeries() as $index => $series) {
                $assignedSeries[] = $series->getModel()->getId();
            }

            // throw new Exception(count($assignedSeries));

            foreach ($seriesAll as $index => $series) {
                if (in_array($series->getId(), $assignedSeries)) {
                    unset($seriesAll[$index]);
                }
            }
        }

        return $seriesAll;
    }

}