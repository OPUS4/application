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
 */


class Frontdoor_IndexController extends Zend_Controller_Action
{

    public function indexAction()
    {

      $request = $this->getRequest();
      $docId = $request->getParam('docId');
      $docId = $this->getRequest()->getParam('docId');
      $document = new Opus_Model_Document($docId);
      $this->document_data = $document->toArray();



      function cmp_weight ($a, $b)
      {

          $weight = array(
                          'Urn' => -40,
                          //'Url' => -35,
                          'TitleMain' => -30,
                          'TitleParent' => -25,
                          'PersonAutor' => -20,
                          'CreatingCorporation' => -15,
                          //'SubjectSwd' => -13,
                          //'SubjectDdc' => -12,
                          //'SubjectUncontrolled => -11;
                          'ContributingCorporation' => -10,
                          'CompletedYear' => -5,
                          'CompletedDate' => -3,
                          'DocumentType' => 0,
                          'PageNumber' => 5,
                          'Edition' => 10,
                          'Issue'   => 15,
                          'Language' => 20,
                          'TitleAbstract' => 25,
                          'Isbn' => 30,
                          'Licence' => 35,
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

      function cmp_title_weight ($a, $b)
      {
          $lang_a = $a['TitleAbstractLanguage'];
          $lang_b = $b['TitleAbstractLanguage'];

          $weight = array ('de' => -30, 'en' => 0);

          $a_weight = $weight[$lang_a];
          $b_weight = $weight[$lang_b];

          if ($a_weight === $b_weight);
          {
              return 0;
          }
       }

    function cmp_abstract_weight ($a, $b)
      {
          $lang_a = $a['TitleAbstractLanguage'];
          $lang_b = $b['TitleAbstractLanguage'];

          $weight = array ('de' => -30, 'en' => 0);

          $a_weight = $weight[$lang_a];
          $b_weight = $weight[$lang_b];

          if ($a_weight === $b_weight);
          {
              return 0;
          }
       }

       function my_sort(array $a)
       {
           $cp = $a;
           uksort($cp, 'cmp_weight');
           usort($cp['TitleMain'], 'cmp_title_weight');
           usort($cp['TitleAbstract'], 'cmp_abstract_weight');
           //uksort($cp, 'cmp_licence_weight');
           return $cp;

       }

     //$this->array = $array = $document;
     $result = my_sort($this->document_data);
     $this->view->result = $result;
     //$this->view = print_r($this->document_data);
     $this->view = print_r($result);

  }
}
