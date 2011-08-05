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
 * @package     Module_Publish Unit Test
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Publish_Model_DepositTest extends ControllerTestCase {
    
    /**
     * @expectedException Publish_Model_FormDocumentNotFoundException
     */
    public function testInvalidDocumentState() {
        $session = new Zend_Session_Namespace('Publish');        
        $document = new Opus_Document();
        $document->setServerState('published');
        $session->documentId = $document->store();
        
        $dep = new Publish_Model_Deposit();        
    }
    
    public function testValidDocumentData() {
        $session = new Zend_Session_Namespace('Publish');        
        $document = new Opus_Document();
        $document->setServerState('temporary');
        $session->documentId = $document->store();
        
        $data = array(
            'PersonSubmitterFirstName1' => 'Hans',
            'PersonSubmitterLastName1' => 'Hansmann',
            'PersonSubmitterEmail1' => 'test@mail.com',
            'PersonSubmitterPlaceOfBirth1' => 'Stadt',
            'PersonSubmitterDateOfBirth1' => '1970/01/01',
            'PersonSubmitterAcademicTitle1' => 'Dr.',
            'PersonSubmitterAllowEmailContact1' => '0',
            'CompletedDate' => '2011/03/03',            
            'EnrichmentLegalNotices' => '1',
            'TitleMain1' => 'Irgendwas',
            'TitleMainLanguage1' => 'deu',
            'TitleMain2' => 'Irgendwas sonst',
            'TitleMainLanguage2'  => 'eng',
            'Language'  => 'deu',
            'SubjectUncontrolled1'  => 'Keyword',
            'Note' => 'Dies ist ein Kommentar',
            'Licence' => 'ID:3',
            'IdentifierUrn' => 'blablup987',
            'Institute'  => '',
            'ThesisGrantor'  => '',
            'SubjectMSC1'  => 'ID:8030',
            'SubjectSwd1'  => 'hallo098',
            'ThesisPublisher' => 'ID:1',
            'SubjectPACS1'  => 'ID:2878',
            'IdentifierOld' => 'blablup987',
            'IdentifierSerial' => 'blablup987',
            'IdentifierUuid'  => 'blablup987',
            'IdentifierIsbn'  => 'blablup987',
            'IdentifierDoi' => 'blablup987',
            'IdentifierHandle'  => 'blablup987',
            'IdentifierUrl' => 'blablup987',
            'IdentifierIssn'  => 'blablup987',
            'IdentifierStdDoi' => 'blablup987',
            'IdentifierCrisLink' => 'blablup987',
            'IdentifierSplashUrl' => 'blablup987',
            'IdentifierOpus3' => 'blablup987',
            'IdentifierOpac' => 'blablup987',
            'ReferenceIsbn' => 'blablup987',
            'ReferenceUrn' => 'blablup987',
            'ReferenceHandle' => 'blablup987',
            'ReferenceDoi' => 'blablup987',
            'ReferenceIssn' => 'blablup987',
            'ReferenceUrl' => 'blablup987',
            'ReferenceCrisLink' => 'blablup987',
            'ReferenceStdDoi' => 'blablup987',
            'ReferenceSplashUrl' => 'blablup987',
            'Series1' => '15986',
            'SeriesNumber1' => '3'
        );
        
        $dep = new Publish_Model_Deposit($data);  
        
        $document = $dep->getDocument();
//        $submitterFirst = $document->getPersonSubmitter()->getFirstName();
//        $this->assertTrue($submitterFirst == 'Hans');
    }
    
}

?>
