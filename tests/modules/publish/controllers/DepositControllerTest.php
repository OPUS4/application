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
 * @package     Tests
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Publish_DepositControllerTest extends ControllerTestCase {

    /**
     * Method tests the deposit action with GET request which leads to a redirect (code 302)
     */
    public function testdepositActionWithoutPost() {
        $this->dispatch('/publish/deposit/deposit');
        $this->assertResponseCode(302);
        $this->assertController('deposit');
        $this->assertAction('deposit');
    }

    /**
     * Method tests the deposit action with invalid POST request
     * which leads to a Error Message and code 200
     */
    public function testDepositActionWithValidPostAndBackButton() {
        $defaultNS = new Zend_Session_Namespace('Publish');
        $defaultNS->elements = array(
            'PersonAuthor1FirstName' => 'Susanne',
            'PersonAuthor1LastName' => 'Gottwald',
            'Language' => 'eng',
            'Institute1' => 'Zuse Institute Berlin (ZIB)',
            'TitleMain1' => 'Entenhausen',
            'TitleMain1Language' => 'eng',
            'SubjectMSC1' => '00A09'
        );

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'back' => 'button_label_back'
                ));

        $this->dispatch('/publish/deposit/deposit');
//        $this->assertResponseCode(200);
//        $this->assertController('deposit');
//        $this->assertAction('deposit');
    }

    /**
     * Method tests the deposit action with a valid POST request
     * which leads to a OK Message, code 200 and Saving of all document data
     */
    public function testDepositActionWithValidPostAndSendButton() {
        $defaultNS = new Zend_Session_Namespace('Publish');
        $defaultNS->elements = array(
            'PersonAuthor1FirstName' => 'Susanne',
            'PersonAuthor1LastName' => 'Gottwald',
            'Language' => 'eng',
            'Institute1' => 'Zuse Institute Berlin (ZIB)',
            'TitleMain1' => 'Entenhausen',
            'TitleMain1Language' => 'eng',
            'SubjectMSC1' => '00A09'
        );
        $defaultNS->documentId = '712';
        $defaultNS->documentType = 'preprint';

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'send' => 'button_label_send'
                ));

        $this->dispatch('/publish/deposit/deposit');
//        $this->assertResponseCode(200);
//        $this->assertController('deposit');
//        $this->assertAction('deposit');
    }

}

?>
