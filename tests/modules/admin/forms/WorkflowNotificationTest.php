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
 * @category    Tests
 * @package     Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Admin_Form_WorkflowNotificationTest extends ControllerTestCase
{

    private $doc;

    protected function setUpTestDocument()
    {
        $doc = $this->createTestDocument();

        $author = new Opus_Person();
        $author->setFirstName('John');
        $author->setLastName('Tester');
        $author->setEmail('john@example.org');
        $doc->addPersonAuthor($author);

        $author = new Opus_Person();
        $author->setFirstName('Jane');
        $author->setLastName('Doe');
        $author->setEmail('jane@example.org');
        $doc->addPersonAuthor($author);

        // This email is used twice for different authors (John & Anton)
        $author = new Opus_Person();
        $author->setFirstName('Anton');
        $author->setLastName('Other');
        $author->setEmail('john@example.org');
        $doc->addPersonAuthor($author);

        // Jim doesn't have an email address and won't be a recipient
        $author = new Opus_Person();
        $author->setFirstName('Jim');
        $author->setLastName('Busy');
        $doc->addPersonAuthor($author);

        // Jane is author and submitter
        $submitter = new Opus_Person();
        $submitter->setFirstName('Jane');
        $submitter->setLastName('Doe');
        $submitter->setEmail('jane@example.org');
        $doc->addPersonSubmitter($submitter);

        // Bob is just submitter
        $submitter = new Opus_Person();
        $submitter->setFirstName('Bob');
        $submitter->setLastName('Writer');
        $submitter->setEmail('bob@example.org');
        $doc->addPersonSubmitter($submitter);

        $this->doc = new Opus_Document($doc->store());
    }

    public function testGetRecipients()
    {
        $this->setUpTestDocument();

        $form = new Admin_Form_WorkflowNotification('published');

        $recipients = $form->getRecipients($this->doc);

        $this->assertEquals([
            'john@example.org' => [
                'name' => [
                    'Tester, John',
                    'Other, Anton'
                ],
                'role' => 'author'
            ],
            'jane@example.org' => [
                'name' => 'Doe, Jane',
                'role' => [
                    'author',
                    'submitter'
                ]
            ],
            'bob@example.org' => [
                'name' => 'Writer, Bob',
                'role' => 'submitter'
            ]
        ], $recipients);
    }

    public function testGetSelectedRecipients() {
        $this->setUpTestDocument();

        $form = new Admin_Form_WorkflowNotification('published');

        $post = [
            'sureyes' => 'Yes',
            'id' => 150,
            'submitter' => '1',
            'author_0' => '1',
            'author_1' => '1',
            'author_2' => '1'
        ];

        $recipients = $form->getSelectedRecipients($this->doc, $post);

        $this->assertCount(2, $recipients);
        $this->assertArrayHasKey('john@example.org', $recipients);
        $this->assertArrayHasKey('jane@example.org', $recipients);

        // TODO check more expectations (array structure)
    }

    /* TODO integrate or delete
     *     /**
     * Add a checkbox for each PersonSubmitter and PersonAuthor (used to select
     * recipients for publish notification email)
     *
     * @param Opus_Document $document
    protected function addPublishNotificationSelection($document)
    {
        $translator = $this->getTranslator();

        $elements = [];

        $recipients = $this->getRecipients($document);

        foreach ($recipients as $recipient) {

        }

        /**
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
        $elements[] = $element->getName();
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
        $elements[] = $element->getName();
        $index++;
        }
        }

        $this->addDisplayGroup($elements, 'notifications', [
            'legend' => 'admin_workflow_notification_headline',
            'description' => 'admin_workflow_notification_description',
        ]);

        $group = $this->getDisplayGroup('notifications');

        $decorators = $group->getDecorators();

        // TODO better way? encapsulation?
        // TODO description is marked by CSS-class 'hint'
        $group->setDecorators([
            $decorators['Zend_Form_Decorator_FormElements'],
            $decorators['Zend_Form_Decorator_HtmlTag'],
            ['description', ['escape' => false, 'placement' => 'PREPEND']],
            $decorators['Zend_Form_Decorator_Fieldset'],
            $decorators['Zend_Form_Decorator_DtDdWrapper'],
        ]);
    }

*/
}
