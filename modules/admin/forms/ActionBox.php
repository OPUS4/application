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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\DocumentInterface;
use Opus\Common\Log;

/**
 * Unterformular für Actionbox für Metadaten-Formular.
 *
 * Die Actiobox zeigt wichtige Statusinformationen zu einem Dokument und bietet
 * Navigation und direkten Zugang zu Funktionen wie Speichern und Abbrechen.
 */
class Admin_Form_ActionBox extends Admin_Form_AbstractDocumentSubForm
{
    public const ELEMENT_SAVE = 'Save';

    public const ELEMENT_CANCEL = 'Cancel';

    /** @var DocumentInterface */
    private $document;

    /** @var Zend_Form|null */
    private $parentForm;

    /**
     * @param null|Zend_Form $parentForm
     * @param array|null     $options
     */
    public function __construct($parentForm = null, $options = null)
    {
        parent::__construct($options);
        $this->parentForm = $parentForm;
    }

    public function init()
    {
        parent::init();

        $element = new Zend_Form_Element_Submit(self::ELEMENT_SAVE);
        $element->setValue('save');
        $element->removeDecorator('DtDdWrapper');
        $element->setLabel('Save');
        $this->addElement($element);

        $element = new Zend_Form_Element_Submit(self::ELEMENT_CANCEL);
        $element->setValue('cancel');
        $element->removeDecorator('DtDdWrapper');
        $element->setLabel('Cancel');
        $this->addElement($element);
    }

    /**
     * @param DocumentInterface $document
     */
    public function populateFromModel($document)
    {
        $this->document = $document;
    }

    /**
     * @param array                  $post
     * @param DocumentInterface|null $document
     */
    public function constructFromPost($post, $document = null)
    {
        $this->document = $document;
    }

    /**
     * @param array $post
     * @param array $context
     * @return string|null
     */
    public function processPost($post, $context)
    {
        if (array_key_exists(self::ELEMENT_SAVE, $post)) {
            return Admin_Form_Document::RESULT_SAVE;
        } elseif (array_key_exists(self::ELEMENT_CANCEL, $post)) {
            return Admin_Form_Document::RESULT_CANCEL;
        }
        return null;
    }

    /**
     * @return DocumentInterface
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        if ($this->parentForm !== null && method_exists($this->parentForm, 'getMessage')) {
            return $this->parentForm->getMessage();
        } else {
            return null;
        }
    }

    /**
     * @return array
     */
    public function getJumpLinks()
    {
        $links = [];

        if ($this->parentForm !== null) {
            $subforms = $this->parentForm->getSubForms();

            foreach ($subforms as $name => $subform) {
                if ($subform->getDecorator('Fieldset') !== null) {
                    // Unterformular mit Fieldset
                    $legend = $subform->getLegend();
                    if ($legend !== null && strlen(trim($legend)) !== 0) {
                        $links['#fieldset-' . $name] = $legend;
                    }
                }
            }
        } else {
            // Sollte niemals passieren
             Log::get()->err('ActionBox without parent form');
        }

        return $links;
    }

    /**
     * @return array
     */
    public function getStateLinks()
    {
        $links = [];

        $workflow = Zend_Controller_Action_HelperBroker::getStaticHelper('workflow');

        $targetStates = $workflow->getAllowedTargetStatesForDocument($this->document);

        foreach ($targetStates as $targetState) {
            $links[$targetState] = [
                'module'      => 'admin',
                'controller'  => 'workflow',
                'action'      => 'changestate',
                'docId'       => $this->document->getId(),
                'targetState' => $targetState,
            ];
        }

        return $links;
    }

    /**
     * @return array
     */
    public function getViewActionLinks()
    {
        $actions = [];

        $docId = $this->document->getId();

        $actions['edit'] = [
            'module'     => 'admin',
            'controller' => 'document',
            'action'     => 'edit',
            'id'         => $docId,
        ];

        $actions['files'] = [
            'module'     => 'admin',
            'controller' => 'filemanager',
            'action'     => 'index',
            'id'         => $docId,
        ];

        /*
        $actions['copy'] = [
            'module' => 'admin',
            'controller' => 'document',
            'action' => 'copy',
            'id' => $docId
        ];*/

        $actions['frontdoor'] = [
            'module'     => 'frontdoor',
            'controller' => 'index',
            'action'     => 'index',
            'docId'      => $docId,
        ];

        return $actions;
    }

    public function loadDefaultDecorators()
    {
        $this->setDecorators(
            [
                [
                    'ViewScript',
                    ['viewScript' => 'actionbox.phtml', 'viewModule' => 'admin'],
                ],
            ]
        );
    }

    public function prepareRenderingAsView()
    {
        $this->setViewModeEnabled();
    }

    /**
     * @return bool
     */
    public function isNavigationEnabled()
    {
        return $this->parentForm !== null;
    }
}
