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
 * The controller produces an (3-dimensional) array with all fields from
 * ModelDocument. This array has to be proofed for occupied values, then reduced
 * to one dimension with recursive iteration and pass through a filter using a
 * function concerning relevance (Stopwords). Keywords are collected and combined
 * with language information, converted to strings and added to the resulting array
 * $mydocdata.
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
                                    $mydoc_data_all[$key. '_' .$key1. '_' .$key2. '_' .$key3] = $value3;
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
        print_r ($mydoc_data);


        // Collecting SWD-Keywords and and combining them with language information
        $myswd_value = Array();
        $myswd_lan = Array();
        foreach ($mydoc_data as $key => $value)
        {
            for ($i = 0; $i < 20; $i++)
            {
                if ($key == 'SubjectSwd_'.$i.'_Value')
                {
                    $myswd_value[] = $value;
                }
                if ($key == 'SubjectSwd_'.$i.'_Language')
                {
                    $myswd_lan[] = $value;
                }
            }
        }
        $mykey_eng = Array();
        $mykey_ger = Array();
        if (array_key_exists ('0', $myswd_lan) == true)
        {
            foreach ($myswd_lan as $key => $value)
            {
               if ($value == 'de')
               {
                  $mykey_ger[] = $key;
               }
               if ($value == 'en')
               {
                $mykey_eng[] = $key;
               }
            }
         }
        $myswd_ger = Array();
        $myswd_eng = Array();
        if (array_key_exists ('0', $myswd_value) == true)
        {
            foreach ($myswd_value as $key => $value)
            {
               if ( in_array($key, $mykey_ger, true))
               {
                 $myswd_ger[] = $value;
               }
               if ( in_array($key, $mykey_eng, true))
               {
                 $myswd_eng[] = $value;
               }
            }
        }
        $swd_eng = Array();
        $swd_ger = Array();
        if (array_key_exists ('0', $myswd_eng) == true)
        {
           $swd_eng = implode (', ' , $myswd_eng);
           $mydoc_data['Swd_eng'] = $swd_eng;
        }
        if (array_key_exists ('0', $myswd_ger) == true)
        {
          $swd_ger = implode (', ' , $myswd_ger);
          $mydoc_data['Swd_ger'] = $swd_ger;
        }

        // Collecting uncontrolled Keywords and and combining them with language information
        $myuncont_lan = array();
        $myuncont_value = array();
        foreach ($mydoc_data as $key => $value)
        {
            for ($i = 0; $i < 20; $i++)
            {
                if ($key == 'SubjectUncontrolled_'.$i.'_Value')
                {
                    $myuncont_value[] = $value;
                }
                if ($key == 'SubjectUncontrolled_'.$i.'_Language')
                {
                    $myuncont_lan[] = $value;
                }
            }
        }
        $mykey_uncont_eng = Array();
        $mykey_uncont_ger = Array();
        foreach ($myuncont_lan as $key => $value)
        {
            if ($value == 'de')
            {
                $mykey_uncont_ger[] = $key;
            }
            if ($value == 'en')
            {
                $mykey_uncont_eng[] = $key;
            }
        }
        $myuncont_ger = Array();
        $myuncont_eng = Array();
        foreach ($myuncont_value as $key => $value)
        {
            if ( in_array($key, $mykey_uncont_ger, true))
            {
               $myuncont_ger[] = $value;
            }
            if ( in_array($key, $mykey_uncont_eng, true))
            {
               $myuncont_eng[] = $value;
            }
        }
        if (array_key_exists ('0', $myuncont_eng) == true)
        {
            $uncont_eng = implode (', ' , $myuncont_eng);
            $mydoc_data['Uncontrolled_eng'] = $uncont_eng;
        }
        if (array_key_exists ('0', $myuncont_ger) == true)
        {
            $uncont_ger = implode (', ' , $myuncont_ger);
            $mydoc_data['Uncontrolled_ger'] = $uncont_ger;
        }
        $this->view->mydoc_data = $mydoc_data;
    }


    /**
     * List with stopwords for omitting irrelevant fields
     *
     * @var array
     */
    private $__stopwords = array('Active', 'CommentInternal', 'DescMarkup',
        'LinkLogo', 'LinkSign', 'MimeType', 'SortOrder', 'PodAllowed', 'ServerDatePublished', 'ServerDateModified',
        'ServerDateUnlocked', 'ServerDateValid', 'Source', 'SwbId', 'PatentCountries', 'PatentDateGranted',
        'PatentApplication', 'Enrichment', 'Email', 'PlaceOfBirth', 'DateOfBirth', 'AcademicTitle');

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

