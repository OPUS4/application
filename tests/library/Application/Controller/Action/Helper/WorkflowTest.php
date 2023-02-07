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

/**
 * Unit Test for class Application_Controller_Action_Helper_Workflow.
 */
class Application_Controller_Action_Helper_WorkflowTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'translation'];

    /** @var Application_Controller_Action_Helper_Workflow */
    private $workflowHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->workflowHelper = new Application_Controller_Action_Helper_Workflow();
    }

    public function testIsValidStateTrue()
    {
        $this->assertTrue($this->workflowHelper->isValidState('published'));
    }

    public function testIsValidStateFalse()
    {
        $this->assertFalse($this->workflowHelper->isValidState('notvalid'));
    }

    public function testIsValidStateForNull()
    {
        $this->assertFalse($this->workflowHelper->isValidState(null));
    }

    public function testIsValidStateForAllStates()
    {
        $states = Application_Controller_Action_Helper_Workflow::getAllStates();

        foreach ($states as $state) {
            $this->assertTrue(
                $this->workflowHelper->isValidState($state),
                'State \'' . $state . '\' should be valid.'
            );
        }
    }

    public function testgetAllStates()
    {
        $states = Application_Controller_Action_Helper_Workflow::getAllStates();

        $this->assertEquals(7, count($states));
        $this->assertTrue(in_array('removed', $states));
    }

    public function testGetAllowedTargetStatesForDocument()
    {
        $doc = $this->createTestDocument();

        $doc->setServerState('unpublished');

        $targetStates =
            $this->workflowHelper->getAllowedTargetStatesForDocument($doc);

        $this->assertEquals(5, count($targetStates));
        $this->assertFalse(in_array('unpublished', $targetStates));
    }

    public function testGetTargetStatesForRemoved()
    {
        $targetStates = Application_Controller_Action_Helper_Workflow::getTargetStates('removed');

        $this->assertEquals(0, count($targetStates));
    }

    public function testGetTargetStatesForInvalidState()
    {
        $targetStates = Application_Controller_Action_Helper_Workflow::getTargetStates('invalid');

        $this->assertEquals(0, count($targetStates));
    }

    public function testGetTargetStatesForNull()
    {
        $targetStates = Application_Controller_Action_Helper_Workflow::getTargetStates(null);

        $this->assertEquals(0, count($targetStates));
    }

    public function testChangeStateToPublished()
    {
        $doc = $this->createTestDocument();

        $doc->setServerState('unpublished');

        $doc->store();

        $docId = $doc->getId();

        $this->workflowHelper->changeState($doc, 'published');

        $doc = Document::get($docId);

        $this->assertEquals('published', $doc->getServerState());
        $this->assertNotNull($doc->getServerDatePublished());
    }

    public function testChangeStateToDeleted()
    {
        $doc = $this->createTestDocument();

        $doc->setServerState('published');

        $doc->store();

        $docId = $doc->getId();

        $this->workflowHelper->changeState($doc, 'deleted');

        $doc = Document::get($docId);

        $this->assertEquals('deleted', $doc->getServerState());
    }

    public function testChangeStateToRemoved()
    {
        $doc = $this->createTestDocument();

        $doc->setServerState('published');

        $doc->store();

        $docId = $doc->getId();

        $this->workflowHelper->changeState($doc, 'removed');

        $documentsHelper = new Application_Controller_Action_Helper_Documents();

        $this->assertNull($documentsHelper->getDocumentForId($docId));
    }

    /**
     * TODO unit test must be modified as soon as 'unpublish' is forbidden
     */
    public function testChangeStateToUnpublished()
    {
        $doc = $this->createTestDocument();

        $doc->setServerState('published');

        $doc->store();

        $docId = $doc->getId();

        $this->workflowHelper->changeState($doc, 'unpublished');

        $doc = Document::get($docId);

        $this->assertEquals('unpublished', $doc->getServerState());
    }

    public function testIsAllowedTransitionTrue()
    {
        $doc = $this->createTestDocument();

        $doc->setServerState('unpublished');

        $this->assertTrue($this->workflowHelper->isTransitionAllowed(
            $doc,
            'published'
        ));
    }

    public function testIsAllowedTransitionFalse()
    {
        $doc = $this->createTestDocument();

        $doc->setServerState('published');

        $this->assertFalse($this->workflowHelper->isTransitionAllowed(
            $doc,
            'unpublished'
        ));
    }

    public function testWorkflowTranslationsForStates()
    {
        $states = Application_Controller_Action_Helper_Workflow::getAllStates();

        $translate = Application_Translate::getInstance();

        foreach ($states as $state) {
            $key = 'admin_workflow_' . $state;
            $this->assertTrue(
                $translate->isTranslated($key),
                'Translation key \'' . $key . '\' is missing.'
            );
        }
    }

    /**
     * OPUSVIER-2446 Regression Test
     */
    public function testRegression2446DontSetServerDatePublished()
    {
        $doc = $this->createTestDocument();

        $doc->setLifecycleListener(new DocumentLifecycleListenerMock());
        $doc->setServerState('unpublished');

        $this->workflowHelper->changeState($doc, 'published'); // Document is stored in this function

        $this->assertEquals('published', $doc->getServerState());

        // ServerDatePublished should not have been set by changeState (can only be tested if this is disabled for
        // the store() function)
        $this->assertNull($doc->getServerDatePublished());
    }
}
