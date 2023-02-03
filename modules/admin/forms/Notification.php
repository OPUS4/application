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
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\DocumentInterface;
use Opus\Common\PersonInterface;

/**
 * TODO fix processing of elements in WorkflowController (in subform names get prefix)
 * TODO adjust styling of sub form
 * TODO unit testing
 */
class Admin_Form_Notification extends Admin_Form_AbstractDocumentSubForm
{
    /** @var DocumentInterface */
    private $document;

    public function init()
    {
        parent::init();

        $this->setLegend('admin_workflow_notification_headline');

        $translator = $this->getTranslator();

        $this->addElement('note', 'description', [
            'value' => "<p>{$translator->translate('admin_workflow_notification_description')}</p>",
        ]);

        $this->setDecorators([
            'FormElements',
            ['ViewScript', ['viewScript' => 'multicheckboxtable.phtml']],
            ['Fieldset', ['class' => 'headline']],
        ]);
    }

    /**
     * add a checkbox for each PersonSubmitter and PersonAuthor (used to select
     * recipients for publish notification email)
     *
     * @param DocumentInterface $document
     *
     * TODO css notification-option, option-not-available
     * TODO check if submitter matches author -> change type (labeling)
     */
    public function addPublishNotificationSelection($document)
    {
        $this->document = $document;
    }

    /**
     * @return array
     */
    public function getRows()
    {
        $translator = $this->getTranslator();

        $document = $this->document;

        $submitters = $document->getPersonSubmitter();

        $submitter = null;

        $options = [];

        if ($submitters !== null && count($submitters) > 0) {
            $submitter            = $submitters[0]->getModel();
            $option               = $this->personToArray($submitters[0]);
            $option['value']      = 'submitter';
            $option['type']       = $translator->translate('admin_workflow_notification_submitter');
            $option['checked']    = 1;
            $options['submitter'] = $option;
        }

        $authors = $document->getPersonAuthor();

        if ($authors !== null) {
            foreach ($authors as $index => $author) {
                $person = $author->getModel();
                if (
                    $submitter !== null
                    && $submitter->matches($person)
                    && $submitter->getEmail() === $person->getEmail()
                ) {
                    $msg = $translator->translate('admin_workflow_notification_submitter_and_author');

                    $options['submitter']['type'] = sprintf($msg, $index + 1);
                } else {
                    $option            = $this->personToArray($author);
                    $option['value']   = 'author_' . $index;
                    $pos               = $index + 1;
                    $option['type']    = "$pos. {$translator->translate('admin_workflow_notification_author')}";
                    $option['checked'] = 1; // TODO LAMINAS BUG old code "$pos = 1 ? '1' : '0';" always "1"
                    $options[]         = $option;
                }
            }
        }

        return $options;
    }

    /**
     * @param PersonInterface $person
     * @return array
     */
    public function personToArray($person)
    {
        $translator = $this->getTranslator();

        $email = trim($person->getEmail() ?? '');

        $disabled = false;

        if ($email === '') {
            $email    = " ({$translator->translate('admin_workflow_notification_noemail')})";
            $disabled = true;
        }

        return [
            'name'     => $person->getDisplayName(),
            'email'    => $email,
            'disabled' => $disabled,
        ];
    }
}
