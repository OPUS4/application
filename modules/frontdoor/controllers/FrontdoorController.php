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
 * @author      Wolfgang Filter (wolfgang.filter@ub.uni-stuttgart.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */


require_once 'Zend/Controller/Action.php';
require_once 'Opus/Document/Document.php';
require_once 'Zend/Db/Table.php';


class Frontdoor_FrontdoorController extends Zend_Controller_Action
{

    public function indexAction()
    {
        $docId = 90;
        $this->view->pagetitle = 'Frontdoor';
        $this->view->header = 'Show dataset with document_id=' . $docId;
        $this->view->body = 'This is where the content would go (Metadata)';


        $doc = new Opus_Document_Document($docId);
        $myresultarray = array();
        $resultarray = $doc->getAllFieldValues($docId);

        foreach ($resultarray as $key => $value)
        {
            // $this->view->value .= "Tabelle" . $key . "<p>";          Überschrift für Testausgabe
            foreach ($resultarray[$key] as $key2 => $value2)
            {
                if ($key === 0)                                      // Tabelle "documents" (Feld [0])
                {
                    if ($value2)                                     // Prüfen, ob Eintragungen vorhanden sind
                    {
                        $this->view->value .= $key2 . ": ";          // Ausgabe und Eintragung in myresultarray
                        $this->view->value .= $value2 . "<br>";
                        $myresultarray[$key2] = $value2;

                        if ($key2 == "publisher_name")
                        {
                            $this->view->my_publisher_name .= $resultarray[$key][$key2];
                        }
                        if ($key2 == "published_year")
                        {
                            $this->view->my_published_year .= $resultarray[$key][$key2];
                        }
                        if ($key2 == "publisher_place")
                        {
                            $this->view->my_publisher_place .= $resultarray[$key][$key2];
                        }
                    }
                }
                else                                                 // ENUM-Felder
                {
                    foreach ($resultarray[$key][$key2] as $key3 => $value3)
                    {
                        if ($value3)                                 // Prüfen ob Eintragungen vorhanden sind
                        {
                            $this->view->value .= $key3 . ": ";      // Ausgabe und Eintrag in myresultarray
                            $this->view->value .= $value3 . "<br>";
                            $myresultarray[$key3] = $value3;

                            if ($value3 == "swd")                    // swd-Felder sammeln und ausgeben
                            {
                              $this->view->my_subject_swd .= $resultarray[$key][$key2][subject_value] . " , ";
                            }

                            if ($value3 == "ddc")                   // DDC-Klassifikationen sammeln und ausgeben
                            {
                              $this->view->my_subject_ddc .= $resultarray[$key][$key2][subject_value];
                            }

                            if ($value3 == "main")                  // Titel (main) sammeln und ausgeben
                            {
                              $this->view->my_title_abstract_main .= $resultarray[$key][$key2][title_abstract_value] . "<br><br>";
                            }

                            if ($value3 == "abstract")             // Abstract - Deutsch (und English) ausgeben
                            {
                              $this->view->my_title_abstract_abstract .= $resultarray[$key][$key2][title_abstract_value] . "<br><br>";
                            }

                           // if ($value3 == "abstract" && $value3 == "eng")
                           // {
                           // $this->view->my_title_abstract_abstract_eng .= $resultarray[$key][$key2][title_abstract_value] . "<br>";
                           //}


                            if ($value3 == "url")                    // URL ausgeben
                            {
                              $this->view->my_identifiers_url .= $resultarray[$key][$key2][identifier_value];
                            }

                            if ($value3 == "urn")                    // URN ausgeben
                            {
                              $this->view->my_identifiers_urn .= $resultarray[$key][$key2][identifier_value];
                            }
                         }
                    }
                }
            }

        }

      // Wird später per txt-Datei übergeben

        $this->view->text_subject_swd = "SWD-Schlagwort";
        $this->view->text_title_abstract_abstract = "Kurzfassung";
     // $this->view->text_title_abstract_abstract_eng = "Kurzfassung auf Englisch";
        $this->view->text_subject_ddc = "DDC-Klassifikation";
        $this->view->text_identifiers_url = "URL";
        $this->view->text_identifiers_urn = "URN";
        $this->view->text_publisher_name = "Herausgeber";
        $this->view->text_published_year = "Erscheinungsjahr";
        $this->view->text_publisher_place = "Erscheinungsort";


     // $this->view->ay = print_r($myresultarray);                       Testausgabe

     }
}

