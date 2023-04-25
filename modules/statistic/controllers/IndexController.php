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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Document;
use Opus\Statistic\LocalCounter;

class Statistic_IndexController extends Application_Controller_Action
{
    /**
     * Just for manual testing, not for final opus version
     */
    public function testAction()
    {
        $this->view->title = 'statistic';
        $counter           = LocalCounter::getInstance();
        $form              = new Test();
        print_r($_POST);
        $form->populate($_POST);
        $this->view->form = $form;

        $documentId = $form->getValue('document_id');
        $fileId     = $form->getValue('file_id');
        $ip         = $form->getValue('ip');
        $userAgent  = $form->getValue('user_agent');
        $result     = $counter->count($documentId, $fileId, 'files', $ip, $userAgent);
        if ($result === false) {
            $this->view->doubleClick = true;
        } else {
            $this->view->doubleClick = false;
            $this->view->count       = $result;
        }
        $this->view->userAgent      = $_SERVER['HTTP_USER_AGENT'];
        $this->view->redirectStatus = $_SERVER['REDIRECT_STATUS'];
        $this->view->baseUrl        = $counter->readYears('280');

        //$this->view->pathToGraph = Zend_Registry::getInstance()->;

        //print_r($_SERVER);
        //$registry = Zend_Registry::getInstance();
        //print_r($registry);
    }

    public function indexAction()
    {
        $docId = $this->getRequest()->getParam("docId");
        if (isset($docId) === false) {
            throw new Exception("docId must be set");
        }
        $this->view->docId = $docId;

        $document = Document::get($docId);

        $titles  = $document->getTitleMain();
        $authors = $document->getPersonAuthor();

        $session = new Zend_Session_Namespace();

        if (isset($session->language)) {
            $language = $session->language;
        } else {
            $language = 'en';
        }

        foreach ($titles as $title) {
            if ($title->getLanguage() === $language) {
                $this->view->title = $title->getValue();
            }
        }

        $authorsArray = [];
        foreach ($authors as $author) {
            $authorsArray[] = $author->getName();
        }
        $this->view->authors = implode(', ', $authorsArray);

        //get statistics from db for total count and for image tag (accessibility)
        $statistic         = LocalCounter::getInstance();
        $totalAbstractPage = $statistic->readTotal($docId, 'frontdoor');
        $totalFiles        = $statistic->readTotal($docId, 'files');

        $yearAbstractPage = $statistic->readYears($docId, 'frontdoor');
        $yearFiles        = $statistic->readYears($docId, 'files');

        $this->view->totalAbstractPage = $totalAbstractPage;
        $this->view->totalFiles        = $totalFiles;

        $years = array_merge(array_keys($yearAbstractPage), array_keys($yearFiles));
        if (count($years) === 0) {
            $years = [date('Y')];
        }
        foreach ($years as $year) {
            if (isset($yearFiles[$year]) === false) {
                $yearFiles[$year] = 0;
            }
            if (isset($yearAbstractPage[$year]) === false) {
                    $yearAbstractPage[$year] = 0;
            }
        }
        ksort($yearFiles);
        ksort($yearAbstractPage);

        foreach (array_keys($yearAbstractPage) as $year) {
            $lines[] = $year . ': ' . $yearAbstractPage[$year] . ', ' . $yearFiles[$year];
        }
        $this->view->altTextStat = implode('; ', $lines);
    }
}
