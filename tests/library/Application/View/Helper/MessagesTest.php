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
 * @category    Application Unit Tests
 * @package     Application_View_Helper
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_View_Helper_MessagesTest extends ControllerTestCase
{

    /**
     * TODO how can the flashmessenger be used in tests?
     */
    public function testMessages()
    {
        $this->useEnglish();

        $view = Zend_Registry::get('Opus_View');

        $helper = new Application_View_Helper_Messages();
        $helper->setView($view);

        $messages = [];
        $messages[] = ['message' => 'Hello, world!', 'level' => 'error'];

        $this->assertEquals(
<<<EOT
<div class="messages">
  <div class="error">Hello, world!</div>
</div>

EOT
, $helper->messages($messages)
        );
    }

    public function testMessagesMultiple()
    {
        $this->useEnglish();

        $view = Zend_Registry::get('Opus_View');

        $helper = new Application_View_Helper_Messages();
        $helper->setView($view);

        $messages = [];
        $messages[] = ['message' => 'validation_error_int', 'level' => 'error'];
        $messages[] = ['message' => 'Just a test!', 'level' => 'info'];
        $messages[] = ['message' => 'Without level.'];

        $this->assertEquals(
<<<EOT
<div class="messages">
  <div class="error">Please provide a number.</div>
  <div class="info">Just a test!</div>
  <div class="">Without level.</div>
</div>

EOT
            , $helper->messages($messages)
        );
    }

    public function testMessagesTranslation()
    {
        $this->useEnglish();

        $view = Zend_Registry::get('Opus_View');

        $helper = new Application_View_Helper_Messages();
        $helper->setView($view);

        $messages = [];
        $messages[] = ['message' => 'validation_error_int', 'level' => 'error'];

        $this->assertEquals(
<<<EOT
<div class="messages">
  <div class="error">Please provide a number.</div>
</div>

EOT
            , $helper->messages($messages)
        );
    }

    public function testMessagesNone()
    {
        $helper = new Application_View_Helper_Messages();

        $helper->setView(Zend_Registry::get('Opus_View'));

        $this->assertEquals('', $helper->messages());
    }

    public function testMessageWithoutMessageKey()
    {
        $helper = new Application_View_Helper_Messages();

        $helper->setView(Zend_Registry::get('Opus_View'));

        $this->assertEquals(
<<<EOT
<div class="messages">
  <div>No key for this message.</div>
</div>

EOT
            , $helper->messages(['No key for this message.'])
        );
    }
}
