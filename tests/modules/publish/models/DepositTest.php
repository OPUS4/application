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
            'PersonSubmitterFirstName1'         => array('value' => 'Hans', 'datatype'=>'Person', 'subfield'=>'0'),
            'PersonSubmitterLastName1'          => array('value' => 'Hansmann', 'datatype'=>'Person', 'subfield'=>'1'),
            'PersonSubmitterEmail1'             => array('value' => 'test@mail.com', 'datatype'=>'Person', 'subfield'=>'1'),
            'PersonSubmitterPlaceOfBirth1'      => array('value' => 'Stadt', 'datatype'=>'Person', 'subfield'=>'1'),
            'PersonSubmitterDateOfBirth1'       => array('value' => '1970/01/01', 'datatype'=>'Person', 'subfield'=>'1'),
            'PersonSubmitterAcademicTitle1'     => array('value' => 'Dr.', 'datatype'=>'Person', 'subfield'=>'1'),
            'PersonSubmitterAllowEmailContact1' => array('value' => '0', 'datatype'=>'Person', 'subfield'=>'1'),
            'CompletedDate'                     => array('value' => '2012/1/1', 'datatype'=>'Date', 'subfield'=>'0'),
            'TitleMain1'                        => array('value' => 'Entenhausen', 'datatype'=>'Title', 'subfield'=>'0'),
            'TitleMainLanguage1'                => array('value' => 'deu', 'datatype'=>'Language', 'subfield'=>'1'),
            'TitleMain2'                        => array('value' => 'Irgendwas sonst', 'datatype'=>'Title', 'subfield'=>'0'),
            'TitleMainLanguage2'                => array('value' => 'eng', 'datatype'=>'Language', 'subfield'=>'1'),
            'Language'                          => array('value' => 'deu', 'datatype'=>'Language', 'subfield'=>'0'),                        
            'Note'                              => array('value' => 'Dies ist ein Kommentar', 'datatype'=>'Note', 'subfield'=>'0'),
            'Licence'                           => array('value' => 'ID:3', 'datatype'=>'Licence', 'subfield'=>'0'),                        
            'ThesisGrantor'                     => array('value' => 'ID:1', 'datatype'=>'ThesisGrantor', 'subfield'=>'0'),
            'ThesisPublisher'                   => array('value' => 'ID:2', 'datatype'=>'ThesisPublisher', 'subfield'=>'0'),
            'ThesisYearAccepted'                => array('value' => '2009', 'datatype'=>'Year', 'subfield'=>'0'),
            'SubjectSwd1'                       => array('value' => 'hallo098', 'datatype'=>'Subject', 'subfield'=>'0'),   
            'SubjectUncontrolled1'              => array('value' => 'Keyword', 'datatype'=>'Subject', 'subfield'=>'0'),
            'SubjectUncontrolledLanguage1'      => array('value' => 'deu', 'datatype'=>'Language', 'subfield'=>'1'),              
            'SubjectMSC1'                       => array('value' => 'ID:8030', 'datatype'=>'Collection', 'subfield'=>'0'),
            'SubjectJEL1'                       => array('value' => 'ID:6740', 'datatype'=>'Collection', 'subfield'=>'0'),
            'SubjectPACS1'                      => array('value' => 'ID:2878', 'datatype'=>'Collection', 'subfield'=>'0'),
            'SubjectBKL1'                       => array('value' => 'ID:13874', 'datatype'=>'Collection', 'subfield'=>'0'),
            'IdentifierUrn'                     => array('value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            'IdentifierOld'                     => array('value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            'IdentifierSerial'                  => array('value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            'IdentifierUuid'                    => array('value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            'IdentifierIsbn'                    => array('value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            'IdentifierDoi'                     => array('value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            'IdentifierHandle'                  => array('value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            'IdentifierUrl'                     => array('value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            'IdentifierIssn'                    => array('value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            'IdentifierStdDoi'                  => array('value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            'IdentifierCrisLink'                => array('value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            'IdentifierSplashUrl'               => array('value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            'IdentifierOpus3'                   => array('value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            'IdentifierOpac'                    => array('value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            'ReferenceIsbn'                     => array('value' => 'blablup987', 'datatype'=>'Reference', 'subfield'=>'0'),
            'ReferenceUrn'                      => array('value' => 'blablup987', 'datatype'=>'Reference', 'subfield'=>'0'),
            'ReferenceHandle'                   => array('value' => 'blablup987', 'datatype'=>'Reference', 'subfield'=>'0'),
            'ReferenceDoi'                      => array('value' => 'blablup987', 'datatype'=>'Reference', 'subfield'=>'0'),
            'ReferenceIssn'                     => array('value' => 'blablup987', 'datatype'=>'Reference', 'subfield'=>'0'),
            'ReferenceUrl'                      => array('value' => 'blablup987', 'datatype'=>'Reference', 'subfield'=>'0'),
            'ReferenceCrisLink'                 => array('value' => 'blablup987', 'datatype'=>'Reference', 'subfield'=>'0'),
            'ReferenceStdDoi'                   => array('value' => 'blablup987', 'datatype'=>'Reference', 'subfield'=>'0'),
            'ReferenceSplashUrl'                => array('value' => 'blablup987', 'datatype'=>'Reference', 'subfield'=>'0'),
            'SeriesNumber1'                     => array('value' => '5', 'datatype'=>'SeriesNumber', 'subfield'=>'0'),
            'Series1'                           => array('value' => 'ID:4', 'datatype'=>'Series', 'subfield'=>'1')
        );
        
        $dep = new Publish_Model_Deposit($data);          
        $document = $dep->getDocument();
        $document->store();

    }
    
}

