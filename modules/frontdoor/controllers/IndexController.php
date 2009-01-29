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

          $weight = array('$TitleMain[0][$TitleAbstractValue]' => 3,
                          //'TitleMain[1][1]' => 5,
                          //'PersonAutor[0][LastName]' => 7,
                          //'Language' => 9,
                            'PublishedYear' => 11,
                          //'TitleAbstract[0][TitleAbstractValue]' => 14,
                          //'TitleAbstract[1][TitleAbstractValue]' => 16,
                            'PageNumber' => 18,
                            'Isbn' => 20
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

     function my_sort(array $a)
     {
         $cp = $a;
         uksort($cp, 'cmp_weight');
         return $cp;
     }



     //$this->array = $array = $document;
     $result = my_sort($this->document_data);
     $this->view->result = $result;
     //$this->view = print_r($this->document_data);
     //$this->view = print_r($result);

  }
}
