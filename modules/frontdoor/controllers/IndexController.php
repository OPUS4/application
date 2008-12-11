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

       /**
        * Getting an associative 1-dimensional array with all relevant document metadata
        * Checking up for empty fields
        * Building new array with all occupied fields
        * $docId must be transferred with the URL
        *
        */

    public function indexAction()
    {
        $docId = 75;
        $this->view->frontdoor_pagetitle = 'OPUS Anwendung - Dokumentinformationen';
        $this->view->frontdoor_entry = 'Das angeforderte Dokument ist zugänglich unter';
        $mydummydata = array();

        $dummydata = array(
         'docId' => $docId,
         'urn' => 'urn:nbn:de:gbv:830-opus-907',
         'url' => 'http://doku.b.tu-harburg.de/volltexte/2005/90',
         'title_ger' => 'Überlastpreisgestaltung als skalierbare, effiziente und stabile Überlastabwehr für zukünftige IP-Netzwerke',
         'title_eng' => 'Congestion pricing as scalable, efficient and stable congestion control for future IP networks',
         'author' => 'Zimmermann, Sebastian',
         'swd' => 'IP, Rechnernetz, Überlastung',
         'ddc' => '004 (Datenverarbeitung; Informatik)',
         'uncontrolled_de' => 'Netzwerktechnik , Steuerung , Regelung',
         'uncontrolled_eng' => 'Networktechnology , Controlling',
         'document_type' => 'Dissertation',
         'language' => 'Deutsch',
         'reviewed'=> 'peer',
         'publisher_university' => 'Technische Universität Hamburg-Harburg',
         'publisher_name' => 'Institut für Regelungstechnik',
         'publisher_place' =>  'Hamburg',
         'completed_year' => '2006',
         'published_year' => '',
         'published_date' => '06.04.2007',
         'abstract_ger' => 'In dieser Dissertation werden durch die Anwendung der Überlastpreisgestaltung leistungsfähigere
         verteilte Überlastabwehr-Algorithmen für TCP/IP-Netzwerke entwickelt. Die Theorie der
         Überlast­preisgestaltung basiert auf Wirtschaftstheorien und Optimierungsverfahren und führt
         zu einer optimalen Allokation von Netzwerkressourcen. Dabei werden im Mittel niedrige
         Warteschlangenlängen und gleichzeitig ein hoher Ausnutzungsgrad erzielt. Die Überlastabwehr-Algorithmen
         werden durch Anwendung von Regelungstechnik in Bezug auf Stabilität weiter untersucht.',
         'abstract_eng' => 'This dissertation focuses on the design of more powerful distributed congestion control
         algorithms for TCP/IP networks by utilizing the theory of Congestion Pricing as a mathematical framework.
         Congestion Pricing, a strategy based on economics and optimization theory, leads to a social optimum for
         the entire network while Maintaining low queue sizes and, at the same time, high utilization.
         Using control theory, the stability of congestion control algorithms is further evaluated.'
        );

        foreach ($dummydata as $key => $value) {
            if ($value != NULL) {
            $mydummydata[$key] = $value;
            }
        }
        $this->view->mydummydata = $mydummydata;
    }
}

