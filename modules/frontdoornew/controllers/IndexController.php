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
 *
 *
 *
 * This controller gets an 3-dimensional array with all document fields from ModelDocument.
 * The array has to be proofed for occupied values, then modified to one dimension and pass through
 * filters using functions concerning order and relevance for displaying them in the Frontdoor View.
 * Keywords are collected and combined with language information and converte to strings.
 *
 *
 *
 *
 *
 */
class Frontdoornew_IndexController extends Zend_Controller_Action
{

    public function indexAction()
    {
        $docId = $this->getRequest()->getParam('docId');
        $document = new Opus_Document($docId);
        $documentType = $document->getType();
        $doc_data = $document->toArray();
        //print_r($doc_data);

// Filter for relevant keys and order for Frontdoor view

        $document_data = $this->filterStopwords($doc_data);
        $document_data['DocumentType'] = $this->view->translate($documentType);
        $result = $this->my_sort($document_data);
        //$this->view->result = $result;
        //print_r($result);


//Recursive Iteration of occcupied values in $result

        $arit = new RecursiveArrayIterator($result);
        $ritit = new RecursiveIteratorIterator($arit);
        foreach ($ritit as $key => $value)
        {
            if (empty($value) == false)
            {
               $mydoc_data_values[] = $value;
            }
        }

   // Iteration for keys in $doc_data for max. 2 sub-arrays

        foreach ($result as $key => $value)
        {
           if (empty($value) == false)
           {
              if (is_array($value))
              {
                $array1 = $value;
                foreach ($array1 as $key1 => $value1)
                {
                   if (is_array($value1))
                   {
                     $array2 = $value1;
                     foreach ($array2 as $key2 => $value2)
                     {
                        if (is_array ($value2))
                        {
                        // do nothing!
                        }
                        else
                        {
                           if (empty($value2) == false)
                           {
                           $mydoc_data_keys[] = $key. "_" .$key1. "_" .$key2;
                           }
                        }
                     }
                  }
                  else
                  {
                      if (empty($value1) == false)
                      {
                      $mydoc_data_keys[] = $key. "_" .$key1;
                      }
                  }
               }
             }
             else
             {
                $mydoc_data_keys[] = $key;
             }
          }
        }
        $mydoc_data = array_combine ($mydoc_data_keys, $mydoc_data_values);
        //Test: print_r ($mydoc_data);


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
        //Test: print_r ($myswd_value);
        $mykey_eng = Array();
        $mykey_ger = Array();
        if (array_key_exists ('0', $myswd_lan) == true)
        {
            //$mykey_eng = Array();
            //$mykey_ger = Array();
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
        //Test: print_r ($mykey_ger);
        $myswd_ger = Array();
        $myswd_eng = Array();
        if (array_key_exists ('0', $myswd_value) == true)
        {
            //$myswd_ger = Array();
            //$myswd_eng = Array();
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
        //Test: print_r ($swd_eng);
        //Test: print_r ($swd_ger)
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
        //Test: print_r ($swd_ger);
        //Test: print_r ($mydoc_data);


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
        //Test: print_r ($myuncont_value);
        //Test: print_r ($myuncont_lan);
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
        //Test: print_r ($mykey_uncont_ger);
        //Test: print_r ($mykey_uncont_eng);
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
        //Test: print_r ($myuncont_eng);
        //Test: print_r ($myuncont_ger);
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
        //Test: print_r ($uncont_ger);
        //Test: print_r ($uncont_eng);
        //print_r ($mydoc_data); //Test
        $this->view->mydoc_data = $mydoc_data;
    }


     /**
      *
      * List with stopwords
      *
      */


    private $__stopwords = array('Active', 'CommentInternal', 'DescMarkup',
        'LinkLogo', 'LinkSign', 'MimeType', 'SortOrder', 'PodAllowed', 'ServerDatePublished', 'ServerDateModified',
        'ServerDateUnlocked', 'ServerDateValid', 'Source', 'SwbId', 'PatentCountries', 'PatentDateGranted',
        'PatentApplication', 'Enrichment', 'Email', 'PlaceOfBirth', 'DateOfBirth', 'AcademicTitle');

     /**
      *
      * Filter: Stopwords
      *
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

    /**
     *
     * Filter: Weight (Order)
     *
     */

    private function cmp_weight ($a, $b)
    {

        $weight = array(
                          'IdentifierUrn' => -100,
                          'TitleMain' => -90,
                          'TitleParent' => -80,
                          'PersonAuthor' => -70,
                          'CreatingCorporation' => -60,
                          'ContributingCorporation' => -50,
                          'NonInstituteAffiliation' => -45,
                          'SubjectSwd' => -40,
                          'Swd' => -37,
                          'SubjectDdc' => -30,
                          'SubjectUncontrolled' => -20,
                          'Reviewed' => -15,
                          'PersonReferee' => -10,
                          'PersonContributor' => -8,
                          'PersonEditor' => -5,
                          'PersonOther' => -2,
                          'PublishedYear' => 5,
                          'CompletedYear' => 10,
                          'CompletedDate' => 12,
                          'DateAccepted' => 20,
                          'DocumentType' => 30,
                          'Language' => 35,
                          'PageNumber' => 40,
                          'PageFirst' => 50,
                          'PageLast' => 60,
                          'Edition' => 70,
                          'Issue'   => 80,
                          'Isbn' => 90,
                          'TitleAbstract' => 100,
                          'Licence' => 120,
        );

        if (array_key_exists($a, $weight) === true)
        {
            $a_weight = $weight[$a];
        }
        else
        {
            $a_weight = 0;
        }

        if (array_key_exists($b, $weight) === true)
        {
            $b_weight = $weight[$b];
        }
        else
        {
            $b_weight = 0;
        }

        if ($a_weight === $b_weight)
        {
            return 0;
        }
        return ($a_weight < $b_weight) ? -1 : 1;

    }

    private function cmp_title_weight ($a, $b)
    {
        if ((array_key_exists('Language', $a) === false)
            or (array_key_exists('Language', $b) === false)) {
            return 0;
        }


        $lang_a = $a['Language'];
        $lang_b = $b['Language'];

        $weight = array ('de' => -30, 'en' => 0);

        $a_weight = $weight[$lang_a];
        $b_weight = $weight[$lang_b];

        if ($a_weight === $b_weight);
        {
            return 0;
        }
    }

    private function cmp_abstract_weight ($a, $b)
    {

        if ((array_key_exists('Language', $a) === false)
            or (array_key_exists('Language', $b) === false)) {
            return 0;
        }


        $lang_a = $a['Language'];
        $lang_b = $b['Language'];

        $weight = array ('de' => -30, 'en' => 0);

        $a_weight = $weight[$lang_a];
        $b_weight = $weight[$lang_b];

        if ($a_weight === $b_weight);
        {
            return 0;
        }
    }

    private function my_sort(array $a)
    {
        $cp = $a;
        uksort($cp, array($this, 'cmp_weight'));

        if (array_key_exists('TitleMain', $cp) === true) {
            usort($cp['TitleMain'], array($this, 'cmp_title_weight'));
        }

        if (array_key_exists('TitleAbstract', $cp) === true) {
            usort($cp['TitleAbstract'], array($this, 'cmp_abstract_weight'));
        }
        return $cp;
    }

}


