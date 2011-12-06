<?php
/*
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
 * @category    Application Unit Test
 * @package     Controller_Helper
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Unit Test for class Admin_Model_Workflow.
 */
class Controller_Helper_WorkflowTest extends ControllerTestCase {

    private $__workflowHelper;

    public function setUp() {
        parent::setUp();

        $this->__workflowHelper = new Controller_Helper_Workflow();
    }

    public function testIsValidStateTrue() {
        $this->assertTrue($this->__workflowHelper->isValidState('published'));
    }

    public function testIsValidStateFalse() {
        $this->assertFalse($this->__workflowHelper->isValidState('notvalid'));
    }

    public function testIsValidStateForNull() {
        $this->assertFalse($this->__workflowHelper->isValidState(null));
    }

    public function testIsValidStateForAllStates() {
        $states = $this->__workflowHelper->getAllStates();

        foreach ($states as $state) {
            $this->assertTrue($this->__workflowHelper->isValidState($state),
                    'State \'' . $state . '\' should be valid.');
        }
    }

    public function testgetAllStates() {
        $states = $this->__workflowHelper->getAllStates();

        $this->assertEquals(7, count($states));
        $this->assertTrue(in_array('removed', $states));
    }

    public function testGetAllowedTargetStatesForDocument() {
        $doc = new Opus_Document();

        $doc->setServerState('unpublished');

        $targetStates =
            $this->__workflowHelper->getAllowedTargetStatesForDocument($doc);

        $this->assertEquals(5, count($targetStates));
        $this->assertFalse(in_array('unpublished', $targetStates));
    }

    public function testGetTargetStatesForRemoved() {
       $targetStates = $this->__workflowHelper->getTargetStates('removed');

       $this->assertEquals(0, count($targetStates));
    }

    public function testGetTargetStatesForInvalidState() {
       $targetStates = $this->__workflowHelper->getTargetStates('invalid');

       $this->assertEquals(0, count($targetStates));
    }

    public function testGetTargetStatesForNull() {
       $targetStates = $this->__workflowHelper->getTargetStates(null);

       $this->assertEquals(0, count($targetStates));
    }

    public function testChangeStateToPublished() {
        $doc = new Opus_Document();

        $doc->setServerState('unpublished');

        $doc->store();

        $docId = $doc->getId();

        $this->__workflowHelper->changeState($doc, 'published');

        $doc = new Opus_Document($docId);

        $this->assertEquals('published', $doc->getServerState());
        $this->assertNotNull($doc->getServerDatePublished());

        $doc->delete();
        $doc->deletePermanent();
    }

    public function testChangeStateToDeleted() {
        $doc = new Opus_Document();

        $doc->setServerState('published');

        $doc->store();

        $docId = $doc->getId();

        $this->__workflowHelper->changeState($doc, 'deleted');

        $doc = new Opus_Document($docId);

        $this->assertEquals('deleted', $doc->getServerState());

        $doc->delete();
        $doc->deletePermanent();
    }

    public function testChangeStateToRemoved() {
        $doc = new Opus_Document();

        $doc->setServerState('published');

        $doc->store();

        $docId = $doc->getId();

        $this->__workflowHelper->changeState($doc, 'removed');

        $documentsHelper = new Controller_Helper_Documents();

        $this->assertFalse($documentsHelper->isValidId($docId));
    }

    /**
     * TODO unit test must be modified as soon as 'unpublish' is forbidden
     */
    public function testChangeStateToUnpublished() {
        $doc = new Opus_Document();

        $doc->setServerState('published');

        $doc->store();

        $docId = $doc->getId();

        $this->__workflowHelper->changeState($doc, 'unpublished');

        $documentsHelper = new Controller_Helper_Documents();

        $doc = new Opus_Document($docId);

        $this->assertEquals('unpublished', $doc->getServerState());

        $doc->delete();
        $doc->deletePermanent();
    }

}