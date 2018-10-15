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
 * @package     Module_Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Form for confirming state changes for a document and selecting persons for notification messages.
 *
 * TODO use displaygroup/subform for list of persons (encapsulate for reuse?)
 * TODO handle label and description in a better way
 */
class Admin_Form_DocumentStateChange extends Admin_Form_YesNoForm
{

    const ELEMENT_ID = 'id';

    private $targetState;

    public function __construct($targetState, $options = null)
    {
        parent::__construct($options);
        $this->setTargetState($targetState);
    }

    public function init()
    {
        parent::init();
        $idElement = new Zend_Form_Element_Hidden(self::ELEMENT_ID);
        $this->addElement($idElement);
    }

    public function setTargetState($targetState)
    {
        $this->targetState = $targetState;
    }

    public function getTargetState()
    {
        return $this->targetState;
    }

    public function populateFromModel($document)
    {
        $this->getElement(self::ELEMENT_ID)->setValue($document->getId());

        $config = Zend_Registry::get('Zend_Config');

        if ($this->getTargetState() == 'published' && $this->isNotificationEnabled()) {
            $this->addPublishNotificationSelection($document);
        }
    }

    public function isNotificationEnabled()
    {
        $config = Zend_Registry::get('Zend_Config');

        return ((isset($config->notification->document->published->enabled)
            && $config->notification->document->published->enabled == 1));
    }

    /**
     * Add a checkbox for each PersonSubmitter and PersonAuthor (used to select
     * recipients for publish notification email)
     *
     * @param Opus_Document $document
     */
    protected function addPublishNotificationSelection($document)
    {
        $translator = $this->getTranslator();

        $this->addElement('hidden', 'plaintext', [
            'description' => '<br/><p><strong>' . $translator->translate('admin_workflow_notification_headline')
                . '</strong></p>'
                . '<p>' . $translator->translate('admin_workflow_notification_description') . '</p>',
            'ignore' => true,
            'decorators' => [['Description', ['escape' => false, 'tag' => '']]]
        ]);

        $submitters = $document->getPersonSubmitter();
        if (!is_null($submitters) && count($submitters) > 0) {
            $label = $translator->translate('admin_workflow_notification_submitter') . ' '
                . trim($submitters[0]->getLastName()) . ", " . trim($submitters[0]->getFirstName());
            $element = null;
            if (trim($submitters[0]->getEmail()) == '') {
                // email notification is not possible since no email address is specified for submitter
                $label .= ' (' . $translator->translate('admin_workflow_notification_noemail') . ')';
                $element = new Zend_Form_Element_Checkbox(
                    'submitter', ['checked' => false, 'disabled' => true,
                        'label' => $label]
                );
                $element->getDecorator('Label')->setOption('class', 'notification-option option-not-available');
            }
            else {
                $label .= ' (' . trim($submitters[0]->getEmail()) . ')';
                $element = new Zend_Form_Element_Checkbox('submitter', ['checked' => true, 'label' => $label]);
                $element->getDecorator('Label')->setOption('class', 'notification-option');
            }
            $this->addElement($element);
        }

        $authors = $document->getPersonAuthor();
        if (!is_null($authors)) {
            $index = 1;
            foreach ($authors as $author) {
                $id = 'author_' . $index;
                $label = $index . '. ' . $translator->translate('admin_workflow_notification_author') . ' '
                    . trim($author->getLastName()) . ", " . trim($author->getFirstName());
                $element = null;
                if (trim($author->getEmail()) == '') {
                    // email notification is not possible since no email address is specified for author
                    $label .= ' (' . $translator->translate('admin_workflow_notification_noemail') . ')';
                    $element = new Zend_Form_Element_Checkbox(
                        $id, ['checked' => false, 'disabled' => true, 'label' => $label]
                    );
                    $element->getDecorator('Label')->setOption('class', 'notification-option option-not-available');
                }
                else {
                    $label .= ' (' . trim($author->getEmail()) . ')';
                    $element = new Zend_Form_Element_Checkbox(
                        $id, ['checked' => true, 'label' => 'foo', 'label' => $label]
                    );
                    $element->getDecorator('Label')->setOption('class', 'notification-option');
                }
                $this->addElement($element);
                $index++;
            }
        }
    }
}
