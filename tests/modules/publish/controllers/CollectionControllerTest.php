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
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Publish_CollectionControllerTest extends ControllerTestCase {

    public function testTopActionWithGet() {
        $this->dispatch('/publish/collection/top');
        $this->assertResponseCode(302);
        $this->assertController('collection');
        $this->assertAction('top');
    }

    public function testTopActionWithPost() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    '' => ''
                ));

        $this->dispatch('/publish/collection/top');
        $this->assertResponseCode(200);
        $this->assertController('collection');
        $this->assertAction('top');
    }

    public function testSubActionWithGet() {
        $this->dispatch('/publish/collection/sub');
        $this->assertResponseCode(302);
        $this->assertController('collection');
        $this->assertAction('sub');
    }

    public function testSubActionWithPostAndAbort() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'abortCollection' => ''
                ));

        $session = new Zend_Session_Namespace('Publish');
        $elemente = array(
            1 => array('name' => 'PersonSubmitterFirstName1', 'value' => 'Hans'),
            2 => array('name' => 'PersonSubmitterLastName1', 'value' => 'Hansmann'),
            3 => array('name' => 'PersonSubmitterEmail1', 'value' => 'test@mail.com'),
            4 => array('name' => 'CompletedDate', 'value' => '2011/03/03'),
            5 => array('name' => 'EnrichmentLegalNotices', 'value' => '1'),
            6 => array('name' => 'TitleMain1', 'value' => 'Irgendwas'),
            7 => array('name' => 'TitleMainLanguage1', 'value' => 'deu')
        );
        $session->elements = $elemente;
        $doc = new Opus_Document();
        $doc->setType('preprint');
        $doc->setServerState('temporary');
        $docId = $doc->store();
        $session->documentId = $docId;
        $session->documentType = 'preprint';

        $this->dispatch('/publish/collection/sub');
        $this->assertResponseCode(200);
        $this->assertController('form');
        $this->assertAction('check');
    }

    /**
     * Parent = choose a given parent collection and stay in sub action
     */
    public function testSubActionWithPostAndParent() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'goToParentCollection' => ''
                ));

        $this->dispatch('/publish/collection/sub');
        $this->assertResponseCode(200);
        $this->assertController('collection');
        $this->assertAction('sub');
    }

    /**
     * Sub = choose a given sub collection and stay in sub action
     */
    public function testSubActionWithPostAndSub() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'goToSubCollection' => ''
                ));

        $this->dispatch('/publish/collection/sub');
        $this->assertResponseCode(200);
        $this->assertController('collection');
        $this->assertAction('sub');
    }

    /**
     * Another = choose another collection, store the given leaf collection and go to top action
     */
    public function testSubActionWithPostAndAnotherWithLeaf() {
        $session = new Zend_Session_Namespace('Publish');
        $session->step = 2;
        $session->collection['collection1'] = '16139';
        $session->countCollections = 0;

        $doc = new Opus_Document();
        $doc->setType('preprint');
        $doc->setServerState('temporary');
        $docId = $doc->store();
        $session->documentId = $docId;
        $session->documentType = 'preprint';

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'chooseAnotherCollection' => '',
                ));

//        $this->dispatch('/publish/collection/sub');
//    //    $this->assertResponseCode(200);
//        $this->assertController('collection');
//        $this->assertAction('top');
    }    

    /**
     * Send = Document shall be stored with all given data -> redirects to Deposit
     */
    public function testSubActionWithPostAndSend() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'send' => ''
                ));

        $doc = new Opus_Document();
        $doc->setType('preprint');
        $doc->setServerState('temporary');
        $docId = $doc->store();

        $session = new Zend_Session_Namespace('Publish');
        $elemente = array(
            1 => array('name' => 'PersonSubmitterFirstName1', 'value' => 'Hans'),
            2 => array('name' => 'PersonSubmitterLastName1', 'value' => 'Hansmann'),
            3 => array('name' => 'PersonSubmitterEmail1', 'value' => 'test@mail.com'),
            4 => array('name' => 'CompletedDate', 'value' => '2011/03/03'),
            5 => array('name' => 'EnrichmentLegalNotices', 'value' => '1'),
            6 => array('name' => 'TitleMain1', 'value' => 'Irgendwas'),
            7 => array('name' => 'TitleMainLanguage1', 'value' => 'deu'),
            8 => array('name' => 'PersonSubmitterPlaceOfBirth1', 'value' => 'Stadt'),
            9 => array('name' => 'PersonSubmitterDateOfBirth1', 'value' => '1970/01/01'),
            10 => array('name' => 'PersonSubmitterAcademicTitle1', 'value' => 'Dr.'),
            11 => array('name' => 'PersonSubmitterAllowEmailContact1', 'value' => '0')
        );

        $session->elements = $elemente;
        $session->documentId = $docId;
        $session->documentType = 'preprint';

        $this->dispatch('/publish/collection/sub');
        echo $this->getResponse()->getBody();
        $this->assertResponseCode(302);
        $this->assertController('deposit');
        $this->assertAction('deposit');
    }

}

?>
