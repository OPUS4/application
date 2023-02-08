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

/**
 * Unit Tests fuer Klasse zum Verwalten von Nachrichten.
 */
class Application_Controller_MessageTemplatesTest extends TestCase
{
    /** @var array */
    private $exampleTemplates;

    /** @var Application_Controller_MessageTemplates */
    private $messageTemplates;

    public function setUp(): void
    {
        parent::setUp();
        $this->exampleTemplates = [
            'save_success'   => 'save_success_msg',
            'save_failure'   => ['failure' => 'save_failure_msg'],
            'delete_success' => 'delete_success_msg',
            'delete_failure' => ['failure' => 'delete_failure_msg'],
        ];

        $this->messageTemplates = new Application_Controller_MessageTemplates($this->exampleTemplates);
    }

    public function testConstruct()
    {
        $this->assertEquals($this->exampleTemplates, $this->messageTemplates->getMessages());
    }

    public function testConstructWithoutParam()
    {
        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('Parameter \'messages\' is required');
        $messages = new Application_Controller_MessageTemplates(null);
    }

    public function testConstructWithBadParam()
    {
        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('Parameter \'messages\' is required and must be an array.');
        $messages = new Application_Controller_MessageTemplates('notanarray');
    }

    public function testGetMessages()
    {
        $messages = $this->messageTemplates->getMessages();

        $this->assertEquals(4, count($messages));
        $this->verifyMessages($messages);
    }

    public function testSetMessages()
    {
        $this->messageTemplates->setMessages([
            'save_success' => 'success',
            'save_failure' => ['failure' => 'failure'],
        ]);

        $this->assertEquals('success', $this->messageTemplates->getMessage('save_success'));
        $this->assertEquals(['failure' => 'failure'], $this->messageTemplates->getMessage('save_failure'));
        $this->assertEquals('delete_success_msg', $this->messageTemplates->getMessage('delete_success'));
    }

    public function testGetMessage()
    {
        $this->assertEquals('save_success_msg', $this->messageTemplates->getMessage('save_success'));
        $this->assertEquals(['failure' => 'save_failure_msg'], $this->messageTemplates->getMessage('save_failure'));
    }

    public function testGetMessageUnknownKey()
    {
        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('Message key \'unknownkey\' is not defined.');
        $this->messageTemplates->getMessage('unknownkey');
    }

    public function testSetMessage()
    {
        $this->assertEquals('save_success_msg', $this->messageTemplates->getMessage('save_success'));

        $this->messageTemplates->setMessage('save_success', 'Erfolg!');

        $this->assertEquals('Erfolg!', $this->messageTemplates->getMessage('save_success'));
    }

    public function testSetMessageNew()
    {
        $this->messageTemplates->setMessage('unknownkey', 'Neue Nachricht!');
        $this->assertEquals('Neue Nachricht!', $this->messageTemplates->getMessage('unknownkey'));
    }

    public function testSetMessageArray()
    {
        $message = ['failure' => 'Something bad happened!'];
        $this->messageTemplates->setMessage('newkey', $message);
        $this->assertEquals($message, $this->messageTemplates->getMessage('newkey'));
    }

    public function testSetMessageNull()
    {
        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('Message key \'save_success\' must not be null.');
        $this->messageTemplates->setMessage('save_success', null);
    }

    public function testSetMessageNullUnknownKey()
    {
        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('Message key \'unknownkey\' must not be null.');
        $this->messageTemplates->setMessage('unknownkey', null);
    }

    /**
     * @param array $messages
     */
    private function verifyMessages($messages)
    {
        $this->assertArrayHasKey('save_success', $messages);
        $this->assertArrayHasKey('save_failure', $messages);
        $this->assertArrayHasKey('delete_success', $messages);
        $this->assertArrayHasKey('delete_failure', $messages);
    }
}
