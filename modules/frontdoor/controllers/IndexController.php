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
 * @package     Module_Frontdoor
 * @author      Wolfgang Filter (wolfgang.filter@ub.uni-stuttgart.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 *
 *
 */

/**
 *
 * The controller gets an (4-dimensional) associative array with all fields from
 * ModelDocument. This array is filtered with relevant stopwords for fields which are not relevant
 * for the Frontdoor-View and then converted to one dimension with recursive iteration.
 * Keywords are collected and combined with language information, converted to strings and
 * added to the resulting array $mydocdata.
 *
 */
class Frontdoor_IndexController extends Zend_Controller_Action
{

    /**
     *
     * @return unknown_type
     */
    public function indexAction()
    {
        $docId = $this->getRequest()->getParam('docId');
        $document = new Opus_Document($docId);
        $documentType = $document->getType();
        $doc_data = $document->toArray();

        // Proof existence of files, find out filenumber
        if (is_array ($files = $document->getFile()) === true)
        {
           $fileNumber = count($files);
           $this->view->fileNumber = $fileNumber;
        }
        else
        {
          $this->fileNumber = $fileNumber == 0;
          $this->view->fileNumber = $fileNumber;
        }
        $this->view->docId = $docId;

        // Filter for relevant keys. Getting Document Type
        $document_data = $this->filterStopwords($doc_data);
        $document_data['Type'] = $this->view->translate($documentType);

        // Recursive Iteration in 4 levels in $document_data
        $arit = new RecursiveArrayIterator ($document_data);
        foreach ($arit as $key => $value)
        {
            if ($arit->hasChildren())
            {
                $el1 = $arit->getChildren();
                foreach ($el1 as $key1 => $value1)
                {
                    if ($el1->hasChildren())
                    {
                        $el2 = $el1->getChildren();
                        foreach ($el2 as $key2 => $value2)
                        {
                            if ($el2->hasChildren())
                            {
                                $el3 = $el2->getChildren();
                                foreach ($el3 as $key3 => $value3)
                                {
                                    if ($el3->hasChildren())
                                    {
                                       $el4 = $el3->getChildren();
                                       foreach ($el4 as $key4 => $value4)
                                       {
                                           $mydoc_data_all[$key. '_' .$key1. '_' .$key2. '_' .$key3. '_' .$key4] = $value4;
                                       }
                                    }
                                    else
                                    {
                                        $mydoc_data_all[$key. '_' .$key1. '_' .$key2. '_' .$key3] = $value3;
                                    }
                                }
                            }
                            else
                            {
                                $mydoc_data_all[$key. '_' .$key1. '_' .$key2] = $value2;
                            }
                        }
                    }
                    else
                    {
                        $mydoc_data_all[$key. '_' .$key1] = $value1;
                    }
                }
            }
            else
            {
                $mydoc_data_all[$key] = $value;
            }
        }

        // Proof for occupied values
        foreach ($mydoc_data_all as $key => $value)
        {
            if (empty($value) === false  && ($value == '0000' || $value == '0000-00-00'  || $value == ', ') === false)
            {
                $mydoc_data[$key] = $value;
            }
        }

        //send layout path to view so that icons can be shown in different layouts
        //TODO maybe there is a more elegant way to do this!?
        $theme =Zend_Registry::getInstance()->get('Zend_Config')->theme;
        if (true === empty($theme)) {
            $theme = 'default';
        }
        $this->view->theme = $theme;
        $this->view->layoutPath = $this->view->baseUrl() .'/layouts/'. $theme;

        //increase counter if all conditions are fullfilled (no double click, no spider, etc.)
        $this->view->mydoc_data = $mydoc_data;
        $statistic = Opus_Statistic_LocalCounter::getInstance();
        $statistic->countFrontdoor($docId);
    }
    /**
     * List with stopwords for omitting irrelevant fields
     *
     * @var array
     */

    private $__stopwords = array('Active', 'CommentInternal', 'DescMarkup',
        'LinkLogo', 'LinkSign', 'MimeType', 'SortOrder', 'PodAllowed', 'ServerDatePublished', 'ServerDateModified',
        'ServerDateUnlocked', 'ServerDateValid', 'Source', 'IdentifierOpac', 'PatentCountries', 'PatentDateGranted',
        'PatentApplication', 'Enrichment', 'Email', 'PlaceOfBirth', 'DateOfBirth');

    /**
     * Filter-function: Comparing stopword-list with keys in array
     *
     * @param $fields
     * @return unknown_type
     */

    private function filterStopwords(array &$fields) {
        $result = array();

        foreach ($fields as $key => $value) {
            if ( in_array($key,$this->__stopwords, true) === false ) {
                if (is_array($value) === true) {
                    $value = $this->filterStopwords($value);
                }
                $result[$key] = $value;
            }

        }
        return $result;
    }
}

