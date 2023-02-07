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

use Opus\Common\DocumentInterface;
use Opus\Common\Log;

/**
 * Controller helper for providing workflow support.
 *
 * Implementiert den Workflow ohne Einschränkungen durch Rollen.
 */
class Application_Controller_Action_Helper_Workflow extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Basic workflow configuration.
     *
     * @var Zend_Config_Ini
     */
    private static $workflowConfig;

    /** @var Zend_Acl */
    private $acl;

    /**
     * Gets called when helper is used like method of the broker.
     *
     * @param DocumentInterface $document
     * @return array of strings - Allowed target states for document
     */
    public function direct($document)
    {
        return $this->getAllowedTargetStatesForDocument($document);
    }

    /**
     * Returns true if a requested state is valid.
     *
     * @param string $state
     * @return bool TRUE - only if the state string exists
     */
    public function isValidState($state)
    {
        $states = self::getAllStates();

        return in_array($state, $states);
    }

    /**
     * Returns true if a transition is allowed for a document.
     *
     * @param DocumentInterface $document
     * @param string            $targetState
     * @return bool - True only if transition is allowed
     */
    public function isTransitionAllowed($document, $targetState)
    {
        $allowedStates = $this->getAllowedTargetStatesForDocument($document);

        return in_array($targetState, $allowedStates);
    }

    /**
     * Returns all allowed target states for a document.
     *
     * @param DocumentInterface $document
     * @return array of strings - Possible target states for document
     */
    public function getAllowedTargetStatesForDocument($document)
    {
        $logger = Log::get();

        $currentState = $document->getServerState();

        $targetStates = self::getTargetStates($currentState);

        $acl = $this->getAcl();

        if ($acl !== null) {
            $logger->debug("ACL: got instance");

            $allowedTargetStates = [];

            foreach ($targetStates as $targetState) {
                $resource = 'workflow_' . $currentState . '_' . $targetState;
                if (
                    ! $acl->has(new Zend_Acl_Resource($resource)) || $acl->isAllowed(
                        Application_Security_AclProvider::ACTIVE_ROLE,
                        $resource
                    )
                ) {
                    $allowedTargetStates[] = $targetState;
                } else {
                    $logger->debug("ACL: $resource not allowed");
                }
            }

            return $allowedTargetStates;
        }

        return $targetStates;
    }

    /**
     * Returns all allowed target states for a current state.
     *
     * @param string $currentState All lowercase name of current state
     * @return array of strings - Possible target states for document
     */
    public static function getTargetStates($currentState)
    {
        // special code to handle 'removed' state
        if ($currentState === 'removed') {
            return [];
        }

        $workflow = self::getWorkflowConfig();

        $targetStates = $workflow->get($currentState);

        if (! empty($targetStates)) {
            return $targetStates->toArray();
        } else {
            return [];
        }
    }

    /**
     * Performs state change on document.
     *
     * @param DocumentInterface $document
     * @param string            $targetState
     *
     * TODO enforcing permissions and throwing exceptions (OPUSVIER-1959)
     */
    public function changeState($document, $targetState)
    {
        switch ($targetState) {
            case 'removed':
                $document->delete();
                break;
            default:
                $document->setServerState($targetState);
                $document->store();
                break;
        }
    }

    /**
     * Returns all defined states of workflow model.
     *
     * @return array of string Names of defined states
     */
    public static function getAllStates()
    {
        $workflow = self::getWorkflowConfig();

        return array_keys($workflow->toArray());
    }

    /**
     * Returns an array with resource names for all possible transitions.
     *
     * @return array of strings
     */
    public static function getWorkflowResources()
    {
        $transitions = [];

        $allStates = self::getAllStates();

        foreach ($allStates as $state) {
            $allTargetStates = self::getTargetStates($state);

            foreach ($allTargetStates as $targetState) {
                $transitions[] = "workflow_" . $state . "_" . $targetState;
            }
        }

        return $transitions;
    }

    /**
     * Returns configuration for basic workflow model.
     *
     * @return Zend_Config_Ini
     */
    public static function getWorkflowConfig()
    {
        if (empty(self::$workflowConfig)) {
            self::$workflowConfig = new Zend_Config_Ini(
                APPLICATION_PATH . '/modules/admin/models/workflow.ini'
            );
        }

        return self::$workflowConfig;
    }

    /**
     * Returns the Zend_Acl object or null.
     *
     * @return Zend_Acl
     */
    public function getAcl()
    {
        if ($this->acl === null) {
            $this->acl = Application_Security_AclProvider::getAcl();
        }
        return $this->acl;
    }

    /**
     * @param Zend_Acl $acl
     */
    public function setAcl($acl)
    {
        $this->acl = $acl;
    }
}
