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
 * @copyright   Copyright (c) 2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Oai_Model_DocumentListTest extends ControllerTestCase {

    /**
     * Test list document ids, metadataPrefix=XMetaDissPlus, different intervals
     * list possible intervals containing "2010-06-05"
     */
    public function testQueryWithDoc3() {
        $intervals = array(
            array(),
            array('from'=>'2010-06-04'),
            array('until'=>'2010-06-04'),
            array('from'=>'2010-06-03'),
            array('until'=>'2010-06-05'),
            array('from'=>'2010-06-04', 'until'=>'2010-06-04'),
            array('from'=>'2010-06-03', 'until'=>'2010-06-04'),
            array('from'=>'2010-06-04', 'until'=>'2010-06-05'),
            array('from'=>'2010-06-03', 'until'=>'2010-06-04'),
        );

        foreach ($intervals AS $interval) {
            $oaiRequest = array('verb'=>'ListRecords', 'metadataPrefix'=>'XMetaDissPlus');
            $oaiRequest = array_merge($interval, $oaiRequest);
            
            $docListModel = new Oai_Model_DocumentList();
            $docListModel->_deliveringDocumentStates = array('published', 'deleted');
            $docListModel->_xMetaDissRestriction = array();
            $docIds = $docListModel->query($oaiRequest);

            $this->assertContains(3, $docIds,
               "Response must contain document id 3: " . var_export($interval, true));
        }
    }

    /**
     * Test list document ids, metadataPrefix=XMetaDissPlus, different intervals
     * list possible intervals *NOT* containing "2010-06-05"
     */
    public function testQueryWithoutDoc3() {
        $intervals = array(
            array('from'=>'2010-06-05'),
            array('until'=>'2010-06-03'),
            array('from'=>'2010-06-05', 'until'=>'2010-06-06'),
            array('from'=>'2010-06-02', 'until'=>'2010-06-03'),
        );

        foreach ($intervals AS $interval) {
            $oaiRequest = array('verb'=>'ListRecords', 'metadataPrefix'=>'XMetaDissPlus');
            $oaiRequest = array_merge($interval, $oaiRequest);

            $docListModel = new Oai_Model_DocumentList();
            $docListModel->_deliveringDocumentStates = array('published', 'deleted');
            $docListModel->_xMetaDissRestriction = array();
            $docIds = $docListModel->query($oaiRequest);

            $this->assertNotContains(3, $docIds,
               "Response must NOT contain document id 3: " . var_export($interval, true));
        }
    }

}
