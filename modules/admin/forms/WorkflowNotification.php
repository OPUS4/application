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
 *
 * TODO use getRecipients
 */
class Admin_Form_WorkflowNotification extends Admin_Form_YesNoForm
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

        $this->addElement('hidden', self::ELEMENT_ID);
    }

    public function setTargetState($targetState)
    {
        $this->targetState = $targetState;
    }

    public function getTargetState()
    {
        return $this->targetState;
    }

    public function isNotificationEnabled()
    {
        $config = Zend_Registry::get('Zend_Config');

        return ((isset($config->notification->document->published->enabled)
            && $config->notification->document->published->enabled == 1));
    }

    /**
     * add a checkbox for each PersonSubmitter and PersonAuthor (used to select
     * recipients for publish notification email)
     *
     * @param Opus_Document $document
     * @param Zend_Form $form
     *
     */
    public function populateFromModel($document)
    {
        $this->getElement(self::ELEMENT_ID)->setValue($document->getId());

        if ($this->getTargetState() == 'published' && $this->isNotificationEnabled()) {
            $subform = new Admin_Form_Notification();
            $subform->addPublishNotificationSelection($document);
            $this->addSubForm($subform, 'notification');
        }
    }
    /**
     * Returns list of recipients for document state change notifications.
     *
     * For each recipient name, email and role(s) are returned.
     *
     * Ignore persons without email.
     *
     * @param $document
     * TODO should not be part of the form class (encapsulate in different class)
     */
    public function getRecipients($document) {
        $recipients = [];

        $authors = $document->getPersonAuthor();
        $this->addPersons($recipients, $authors);

        $submitters = $document->getPersonSubmitter();
        $this->addPersons($recipients, $submitters, 'submitter');

        return $recipients;
    }

    protected function addPersons(&$recipients, $persons, $role = 'author')
    {
        foreach ($persons as $index => $person) {
            $fullname = $person->getDisplayName();
            $email = $person->getEmail();
            if (strlen(trim($email)) > 0) {
                if (array_key_exists($email, $recipients)) {
                    $entry = $recipients[$email];
                    $names = $entry['name'];

                    if (!is_array($names) && $names !== $fullname ||
                        is_array($names) && !in_array($fullname, $names)) {
                        if (!is_array($names)) {
                            $names = [$names];
                        }

                        $names[] = $fullname;
                        $entry['name'] = $names;
                    }

                    $roles = $entry['role'];

                    if (!is_array($roles) && $roles !== $role ||
                        is_array($roles) && !in_array($role, $roles)) {
                        if (!is_array($roles)) {
                            $roles = [$roles];

                            $roles[] = $role;
                            $entry['role'] = $roles;
                        }
                    }

                    $recipients[$email] = $entry;
                } else {
                    $recipients[$email] = [
                        'name' => $fullname,
                        'role' => $role
                    ];
                }
            }
        }
    }

    /**
     * Returns the recipients that have been selected in the form.
     * @return
     */
    public function getSelectedRecipients($document, $post)
    {
        $recipients = [];

        $authors = $document->getPersonAuthor();

        $selected = [];

        // TODO $post = $this->getValues();

        foreach ($authors as $index => $author) {
            $key = "author_$index";
            if (isset($post[$key]) && $post[$key] == '1') {
                $selected[] = $authors[$index];
            }
        }

        if (count($selected) > 0) {
            $this->addPersons($recipients, $selected, 'author');
        }

        if (isset($post['submitter']) && $post['submitter'] == '1') {
            $this->addPersons($recipients, [$document->getPersonSubmitter(0)], 'submitter');
        }

        return $recipients;
    }
}
