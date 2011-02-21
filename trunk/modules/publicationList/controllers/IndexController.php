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
 * @package     Module_PublicationList
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id:$
 */

class PublicationList_IndexController extends Controller_Action {

    private $log;
    private $resultList;
    private $renderer;
   
    public function init() {
        $this->log = Zend_Registry::get('Zend_Log');
    }

    public function indexAction() {
        /* TODO: zulässige-Query-parameters
         * theme = 'plain'
         * lang = 'de' o 'eng'
         * id = '...'
         */
        $this->renderer = 'results';
        $this->createPublicationLists();
        $theme = $this->getRequest()->getParam("theme");
        if ($theme === 'plain') {
            $this->_helper->layout->setLayoutPath(APPLICATION_PATH . '/public/layouts/plain');

        }
        $this->view->addFilterPath('View/Filter', 'View_Filter')
            ->addFilter('RemoveWhitespaces');

        $this->render($this->renderer);
    }

    private function createPublicationLists() {
        $config = Zend_Registry::get('Zend_Config');
        $log = Zend_Registry::get('Zend_Log');

        $coll_id = $this->getRequest()->getParam('id');
        $coll = new Opus_Collection($coll_id);
        $coll_name = $coll->getName();

	if ($coll_name === 'Jahresbericht') { $this->renderer = 'annual'; }
	
        $doc_ids = $coll->getDocumentIds();
        
        $publicationSite = new PublicationList_Model_PublicationSite();

        $this->view->title = $coll->getName();
        $this->view->name = $publicationSite->getNameGerman();


        foreach ($doc_ids as $id) {
            $publication = null;

            if ($this->getRequest()->getParam("theme") === 'local') {
                 if (isset($config->publicationlist->local->baseurl)) {
                     if ($this->getRequest()->getParam("lang") === 'eng') {
                        $publication = new PublicationList_Model_Publication($id, $config->publicationlist->local->baseurl->eng);
                        $this->view->name = $publicationSite->getNameEnglish();
                     }
                     else {
                        $publication = new PublicationList_Model_Publication($id, $config->publicationlist->local->baseurl->de);
                     }
                 }
                 else {
                     $publication = new PublicationList_Model_Publication($id);
                 }
            }
            else {
                 if (isset($config->publicationlist->external->baseurl)) {
                     if ($this->getRequest()->getParam("lang") === 'eng') {
                        $publication = new PublicationList_Model_Publication($id, $config->publicationlist->external->baseurl->eng);
                        $this->view->name = $publicationSite->getNameEnglish();
                     }
                     else {
                        $publication = new PublicationList_Model_Publication($id, $config->publicationlist->external->baseurl->de);
                     }
                 }
                 else {
                     $publication = new PublicationList_Model_Publication($id);
                 }
            }


            
            $year = $publication->getPublishedYear();
            $inListe = 0;
            foreach ($publicationSite->getSingleList() as $sl) {
                if ($sl->getYear() === $year) {
                    $sl->addPublication($publication);
                    $inListe = 1;
                }
            }
            if ($inListe === 0) {
                $sl = new PublicationList_Model_SingleList($year);
                $sl->addPublication($publication);
                $publicationSite->addSingleList($sl);
            }

            if ($this->getRequest()->getParam("theme") === 'plain') {
                $publication->setBibtexUrl($publication->getBibtexUrlExternal());
                $publication->setRisUrl($publication->getRisUrlExternal());
                $publication->setPdfUrl($publication->getPdfUrlExternal());
                $publication->setPsUrl($publication->getPsUrlExternal());

                //$publication->setImageAbstract($publication->getImageAbstractExternal());
                $publication->setImageBibtex($publication->getImageBibtexExternal());
                $publication->setImageDoi($publication->getImageDoiExternal());
                $publication->setImagePdf($publication->getImagePdfExternal());
                $publication->setImageRis($publication->getImageRisExternal());

                if(!count($publication->getAuthors()) == 0) {
                    foreach ($publication->getAuthors() as $a) {
                        $a->setUrl($a->getUrlExternal());
                    }
                }
            }

            if ($this->getRequest()->getParam("theme") === 'local') {
                $publication->setBibtexUrl($publication->getBibtexUrlExternal());
                $publication->setRisUrl($publication->getRisUrlExternal());

                //$publication->setImageAbstract($publication->getImageAbstractExternal());
                $publication->setImageBibtex($publication->getImageBibtexLocal());
                $publication->setImageDoi($publication->getImageDoiLocal());
                $publication->setImagePdf($publication->getImagePdfLocal());
                $publication->setImageRis($publication->getImageRisLocal());

                if(!count($publication->getAuthors()) == 0) {
                    foreach ($publication->getAuthors() as $a) {
                        $a->setUrl($a->getUrlLocal());
                    }
                }
            }

        }
        $publicationSite->orderSingleLists();

        $this->view->results = $publicationSite->getSingleList();
    }

}
?>