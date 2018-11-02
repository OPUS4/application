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
 * @category    Tests
 * @package     Application_View_Helper
 * @author      Maximilian Salomon <salomon@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_View_Helper_JavascriptMessagesTest extends ControllerTestCase
{

    private $helper;

    public function setUp()
    {
        parent::setUp();

        $this->useEnglish();

        $this->helper = new Application_View_Helper_JavascriptMessages();

        $this->helper->setView(Zend_Registry::get('Opus_View'));
    }

    /**
     * Tests, if the correct code-snippet for the Messages will be generated.
     */
    public function testJavascriptMessages()
    {
        $Messages = [
            'key1' => 'message1',
            'key2' => 'message2'
        ];
        $this->helper->setMessages($Messages);

        $expectation = '        <script type="text/javascript">' . "\n"
            . '            opus4Messages["key1"] = "message1";' . "\n"
            . '            opus4Messages["key2"] = "message2";' . "\n"
            . '        </script>' . "\n";

        $reality = $this->helper->javascriptMessages();
        $this->assertEquals($expectation, $reality);
    }

    /**
     * Tests, if the addMessage-function translates in the correct way and deliver the correct key-message pairs.
     */
    public function testAddMessage()
    {
        $this->helper->addMessage('key1', 'message1');
        $this->helper->addMessage('identifierInvalidFormat');
        $this->helper->addMessage('testkey');

        $Messages = [
            'key1' => 'message1',
            'identifierInvalidFormat' => "'%value%' is malformed.",
            'testkey' => 'testkey'
        ];

        $this->assertEquals($Messages, $this->helper->getMessages());
    }

    public function testSetMessages()
    {
        $Messages = [
            'key1' => 'message1',
            'key2' => 'message2'
        ];
        $this->helper->setMessages($Messages);
        $this->assertEquals($Messages, $this->helper->getMessages());
    }

    /**
     * If the Messages are empty or set to null, the code snippet for the Messages without any key-message pairs
     * should be delivered. This is tested here.
     */
    public function testSetNullMessages()
    {
        $this->helper->setMessages(null);
        $reality = $this->helper->javascriptMessages();
        $expectation = '        <script type="text/javascript">' . "\n"
            . '        </script>' . "\n";
        $this->assertEquals($expectation, $reality);
    }

    public function testGetMessages()
    {
        $this->assertEquals([], $this->helper->getMessages());
    }
}
