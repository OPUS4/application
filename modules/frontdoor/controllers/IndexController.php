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
        $doc_data = $document->toArray();

        // Display collection pathes
        $collections = $document->getCollections();
        $collection_pathes = array();
        foreach ($collections as $coll_index=>$collection) {
            $coll_data = ($collection->toArray());
            $collection_pathes[$coll_index] = $coll_data['DisplayFrontdoor'];
            $parent = $coll_data;
            while (true === array_key_exists('ParentCollection', $parent)) {
                // TODO: There can be more than one parent
                $parent = $parent['ParentCollection'][0];
                $collection_pathes[$coll_index] = $parent['DisplayFrontdoor'] . ' > ' .$collection_pathes[$coll_index];
            }
            $collection_pathes[$coll_index] = $coll_data['RoleName'] . ': ' . $collection_pathes[$coll_index];
        }
        if (false === empty($collection_pathes)) {
            $this->view->collectionPathes = $collection_pathes;
        }

        // Proof existence of URN, find out urnNumber
        if (is_array ($urn = $document->getIdentifierUrn()) === true)
        {
           $urnNumber = count($urn);
           $this->view->urnNumber = $urnNumber;
        }
        else
        {
            $this->urnNumber = $urnNumber = 0;
            $this->view->urnNnumber = $urnNumber;
        }

        // Proof existence of main titles, find out titleNumber
        if (is_array ($titles = $document->getTitleMain()) === true)
        {
           $titleNumber = count($titles);
           $this->view->titleNumber = $titleNumber;
        }
        else
        {
           $this->titleNumber = $titleNumber = 0;
           $this->view->titleNumber = $titleNumber;
        }

        // Proof existence of files, find out fileNumber
        if (is_array ($files = $document->getFile()) === true)
        {
           $fileNumber = count($files);
           $this->view->fileNumber = $fileNumber;
        }
        else
        {
           $this->fileNumber = $fileNumber = 0;
           $this->view->fileNumber = $fileNumber;
        }

        // Proof existence of authors, find out authorNumber
        if (is_array ($authors = $document->getPersonAuthor()) === true)
        {
           $authorNumber = count($authors);
           $this->view->authorNumber = $authorNumber;
        }
        else
        {
           $this->authorNumber = $authorNumber = 0;
           $this->view->authorNumber = $authorNumber;
        }

        // Proof existence of languages, find out languageNumber
        if (is_array ($languages = $document->getLanguage()) === true)
        {
           $languageNumber = count($languages);
           $this->view->languageNumber = $languageNumber;
        }
        else
        {
           $this->languageNumber = $languageNumber = 0;
           $this->view->languageNumber = $languageNumber;
        }

        //Proof existence of swd-keyword-strings, find out swdNumber
        if (is_array ($swd = $document->getSubjectSwd()) === true)
        {
           $swdNumber = count($swd);
           $this->view->swdNumber = $swdNumber;
        }
        else
        {
           $this->swdNumber = $swdNumber = 0;
           $this->view->swdNumber = $swdNumber;
        }

        // Proof existence of uncontrolled keyword-strings, find out uncontrolledNumber
        if (is_array ($uncontrolled = $document->getSubjectUncontrolled()) === true)
        {
           $uncontrolledNumber = count($uncontrolled);
           $this->view->uncontrolledNumber = $uncontrolledNumber;
        }
        else
        {
           $this->uncontrolledNumber = $uncontrolledNumber = 0;
           $this->view->uncontrolledNumber = $uncontrolledNumber;
        }

        // Proof existence of psyndex-keyword-strings, find out psyndexNumber
        if (is_array ($psyndex = $document->getSubjectPsyndex()) === true)
        {
           $psyndexNumber = count($psyndex);
           $this->view->psyndexNumber = $psyndexNumber;
        }
        else
        {
           $this->psyndexNumber = $psyndexrNumber = 0;
           $this->view->psyndexNumber = $psyndexNumber;
        }

        // Proof existence of isbn, find out isbnNumber (that means the number of isbn values)
        if (is_array ($isbn = $document->getIdentifierIsbn()) === true)
        {
           $isbnNumber = count($isbn);
           $this->view->isbnNumber = $isbnNumber;
        }
        else
        {
           $this->isbnNumber = $isbnNumber = 0;
           $this->view->isbnNumber = $isbnNumber;
        }

        // Proof existence of contributors, find out contributorNumber
        if (is_array ($contributors = $document->getPersonContributor()) === true)
        {
           $contributorNumber = count($contributors);
           $this->view->contributorNumber = $contributorNumber;
        }
        else
        {
           $this->contributorNumber = $contributorNumber = 0;
           $this->view->contributorNumber = $contributorNumber;
        }

        // Proof existence of other persons, find out otherPersonNumber
        if (is_array ($otherPersons = $document->getPersonOther()) === true)
        {
           $otherPersonNumber = count($otherPersons);
           $this->view->otherPersonNumber = $otherPersonNumber;
        }
        else
        {
           $this->otherPersonNumber = $otherPersonNumber = 0;
           $this->view->otherPersonNumber = $otherPersonNumber;
        }

        // Proof existence of referees, find out refereeNumber
        if (is_array ($referees = $document->getPersonReferee()) === true)
        {
           $refereeNumber = count($referees);
           $this->view->refereeNumber = $refereeNumber;
        }
        else
        {
           $this->refereeNumber = $refereeNumber = 0;
           $this->view->refereeNumber = $refereeNumber;
        }

        // Proof existence of editors, find out editorNumber
        if (is_array ($editors = $document->getPersonEditor()) === true)
        {
           $editorNumber = count($editors);
           $this->view->editorNumber = $editorNumber;
        }
        else
        {
           $this->editorNumber = $editorNumber = 0;
           $this->view->editorNumber = $editorNumber;
        }

        // Proof existence of authors, find out authorNumber
        if (is_array ($authors = $document->getPersonAuthor()) === true)
        {
           $authorNumber = count($authors);
           $this->view->authorNumber = $authorNumber;
        }
        else
        {
           $this->authorNumber = $authorNumber = 0;
           $this->view->authorNumber = $authorNumber;
        }

        // Proof existence of abstracts, find out abstractNumber
        if (is_array ($abstracts = $document->getTitleAbstract()) === true)
        {
           $abstractNumber = count($abstracts);
           $this->view->abstractNumber = $abstractNumber;
        }
        else
        {
           $this->abstractNumber = $abstractNumber = 0;
           $this->view->abstractNumber = $abstractNumber;
        }

        // Proof existence of licences, find out licenceNumber
        if (is_array ($licences = $document->getLicence()) === true)
        {
           $licenceNumber = count($licences);
           $this->view->licenceNumber = $licenceNumber;
        }
        else
        {
          $this->licenceNumber = $licenceNumber = 0;
          $this->view->licenceNumber = $licenceNumber;
        }

        $this->view->docId = $docId;

        // Filter for relevant keys. Getting Document Type
        $document_data = $this->filterStopwords($doc_data);
        $document_data['Type'] = $this->view->translate($document_data['Type']);

        // Recursive Iteration with 4 levels in $document_data leads to the one-dimensional array: $my_doc_data
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

    private $__stopwords = array('Active', 'CommentInternal', 'MimeType', 'SortOrder', 'ServerDatePublished', 'ServerDateModified',
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

