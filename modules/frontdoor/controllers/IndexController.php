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


class Frontdoor_IndexController extends Zend_Controller_Action
{

    public function indexAction()
    {
        $docId = 75;
        $this->view->pagetitle = 'Frontdoor';
        $this->view->header = 'Show dataset with document_id=' . $docId;
        $mydummydata = array();

        $dummydata = array("docId" => "75", "author" => "Dipl.-Ing. Friedrich Wolff",
                            "title" => "Überlastpreisgestaltung als skalierbare, effiziente und stabile Überlastabwehr für zukünftige IP-Netzwerke",
                            "abstract" => "In dieser Dissertation werden durch die Anwendung der Überlastpreisgestaltung leistungsfähigere verteilte
                            Überlastabwehr-Algorithmen für TCP/IP-Netzwerke entwickelt. Die Theorie der Überlast­preisgestaltung basiert auf
                            Wirtschaftstheorien und Optimierungsverfahren und führt zu einer optimalen Allokation von Netzwerkressourcen.
                            Dabei werden im Mittel niedrige Warteschlangenlängen und gleichzeitig ein hoher Ausnutzungsgrad erzielt.
                            Die Überlastabwehr-Algorithmen werden durch Anwendung von Regelungstechnik in Bezug auf Stabilität weiter untersucht.",
                            "url" => "http://doku.b.tu-harburg.de/volltexte/2005/90", "urn" => "urn:nbn:de:gbv:830-opus-907",
                            "document_type" => "Dissertation", "swd" => "IP, Rechnernetz, Überlastung",
                            "publisher_name" => "", "ddc" => "620", "publisher_place" => "Berlin", "published_year" => "2006");


        foreach ($dummydata as $key => $value)
        {
            if ($value)
            {
            $mydummydata[$key] = $value;
            }
        }
        $this->view->mydummydata = $mydummydata;
        $this->view->text_title = "Titel";
        $this->view->text_author = "Autor(en)";
        $this->view->text_abstract = "Kurzfassung";
        $this->view->text_url = "URL";
        $this->view->text_publisher_name = "Institut";
        $this->view->text_urn = "URN";
        $this->view->text_document_type = "Dokumenttyp";
        $this->view->text_swd = "SWD-Indexierung:";
        $this->view->text_publisher_place = "Erscheinungsort";
        $this->view->text_published_year = "Jahr der Veröffentlichung";
        $this->view->text_ddc = "DDC-Sachgruppe";
    }
}

