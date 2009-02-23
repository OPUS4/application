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
 * This controller gets an array with all document fields from ModelDocument.
 * These fields pass through filters using functions concerning order and relevance.
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

        //Proof for occupied fields; Only these fields are to be displayed

        $arit = new RecursiveArrayIterator($doc_data);
        $ritit = new RecursiveIteratorIterator($arit);
        foreach ($ritit as $key => $value)
        {
            if (empty($value) == true)
            {

            }
            else
            {
            $mydoc_data[] = $value;
            }
        }
        print_r($mydoc_data);

        $document_data = $this->filterStopwords($doc_data);
        $document_data['DocumentType'] = $this->view->translate($documentType);
        $result = $this->my_sort($document_data);
        $this->view->result = $result;
        //print_r($result);
    }

     /**
      *
      * List with stopwords
      *
      */

    private $__stopwords = array('Active', 'CommentInternal', 'DescMarkup',
        'LinkLogo', 'LinkSign', 'MimeType', 'SortOrder', 'PodAllowed', 'ServerDatePublished', 'ServerDateModified',
        'ServerDateUnlocked', 'ServerDateValid', 'Source', 'SwbId', 'PatentCountries', 'PatentDateGranted',
        'PatentApplication', 'Enrichment', 'Email', 'LastName', 'FirstName', 'PlaceOfBirth', 'DateOfBirth', 'AcademicTitle');

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
                          'Urn' => -100,
                          'TitleMain' => -90,
                          'TitleParent' => -80,
                          'PersonAuthor' => -70,

                          'CreatingCorporation' => -60,
                          'ContributingCorporation' => -50,
                          'NonInstituteAffiliation' => -45,
                          'SubjectSwd' => -40,
                          'SubjectDdc' => -30,
                          'SubjectUncontrolled' => -20,
                          'PersonOther' => -7,
                          'Reviewed' => 0,
                          'PersonReferee' => 5,
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
