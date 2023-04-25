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

use Opus\Common\Date;
use Opus\Common\DnbInstitute;
use Opus\Common\Language;
use Opus\Common\Licence;

/**
 * Unit Tests für Bestaetigungsformular.
 *
 * TODO TRANSLATION use TestCase with Translation (without database)
 */
class Application_Form_ConfirmationTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'translation';

    /** @var Application_Form_Confirmation */
    private $form;

    public function setUp(): void
    {
        parent::setUp();

        $this->form = $this->getForm();
    }

    /**
     * @return Application_Form_Confirmation
     */
    private function getForm()
    {
        return new Application_Form_Confirmation(Licence::class);
    }

    /**
     * @covers Application_Form_Confirmation::__construct
     * @covers Application_Form_Confirmation::init
     */
    public function testConstructForm()
    {
        $form = new Application_Form_Confirmation(Licence::class);

        $this->assertEquals(Licence::class, $form->getModelClass());
        $this->assertEquals(3, count($form->getElements()));
        $this->assertNotNull($form->getElement('Id'));
        $this->assertNotNull($form->getElement('ConfirmYes'));
        $this->assertNotNull($form->getElement('ConfirmNo'));
        $this->assertNotNull($form->getLegend());

        $this->assertEquals(3, count($form->getDecorators()));
        $this->assertNotNull($form->getDecorator('ViewScript'));
        $this->assertNotNull($form->getDecorator('Fieldset'));
        $this->assertNotNull($form->getDecorator('Form'));

        $this->assertEquals('headline', $form->getDecorator('Fieldset')->getOption('class'));
    }

    public function testConstructFormNull()
    {
        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('construct without parameter');
        new Application_Form_Confirmation(null);
    }

    public function testConstructFormEmpty()
    {
        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('construct without parameter');
        new Application_Form_Confirmation('   ');
    }

    public function testGetFormLegend()
    {
        $this->useEnglish();
        $form = new Application_Form_Confirmation(Language::class);

        $legend = $form->getFormLegend();

        $this->assertEquals('Delete Language', $legend);
    }

    public function testGetModelClass()
    {
        $form = new Application_Form_Confirmation(Language::class);

        $this->assertEquals(Language::class, $form->getModelClass());
    }

    public function testGetModelClassName()
    {
        $this->useEnglish();
        $form = new Application_Form_Confirmation(DnbInstitute::class);

        $this->assertEquals('Institute', $form->getModelClassName());
    }

    public function testGetModelDisplayName()
    {
        $form = new Application_Form_Confirmation(Licence::class);
        $form->setModel(Licence::get(4));
        $this->assertContains('Creative Commons - CC BY-ND - Namensnennung', $form->getModelDisplayName());
    }

    public function testGetModelDisplayNameNoModel()
    {
        $form = new Application_Form_Confirmation(Licence::class);
        $this->assertEquals('', $form->getModelDisplayName());
    }

    public function testSetGetModelDisplayName()
    {
        $form = new Application_Form_Confirmation(Licence::class);
        $form->setModel(Licence::get(4));
        $this->assertContains('Creative Commons - CC BY-ND - Namensnennung', $form->getModelDisplayName());

        $form->setModelDisplayName('custom display name');

        $this->assertEquals('custom display name', $form->getModelDisplayName());

        $form->setModelDisplayName(null);

        $this->assertContains('Creative Commons - CC BY-ND - Namensnennung', $form->getModelDisplayName());
    }

    public function testIsConfirmedYes()
    {
        $form = new Application_Form_Confirmation(Language::class);

        $post = [
            'Id'         => '100',
            'ConfirmYes' => 'Ja',
        ];

        $this->assertTrue($form->isConfirmed($post));
    }

    public function testIsConfirmedNo()
    {
        $form = new Application_Form_Confirmation(Language::class);

        $post = [
            'Id'        => '100',
            'ConfirmNo' => 'Nein',
        ];

        $this->assertFalse($form->isConfirmed($post));
    }

    public function testIsConfirmedNoInvalidForm()
    {
        $subform = new Application_Form_Confirmation(Language::class);
        $form    = new Zend_Form();
        $form->addSubForm($subform, 'confirmation');

        $post = [
            'Id'         => '',
            'ConfirmYes' => 'Ja',
        ];

        $this->assertFalse($subform->isConfirmed($post));
        $this->assertEquals(1, count($subform->getErrors()));
    }

    public function testProcessPostYes()
    {
        $form = new Application_Form_Confirmation(Language::class);

        $post = [
            'Id'         => '100',
            'ConfirmYes' => 'Ja',
        ];

        $this->assertEquals(Application_Form_Confirmation::RESULT_YES, $form->processPost($post));
    }

    public function testProcessPostNo()
    {
        $form = new Application_Form_Confirmation(Language::class);

        $post = [
            'Id'        => '100',
            'ConfirmNo' => 'Nein',
        ];

        $this->assertEquals(Application_Form_Confirmation::RESULT_NO, $form->processPost($post));
    }

    public function testValidation()
    {
        $this->assertTrue($this->form->isValid(['Id' => '100']));
        $this->assertFalse($this->form->isValid(['Id' => ' ']));
        $this->assertFalse($this->form->isValid(['Id' => 'abc']));
        $this->assertFalse($this->form->isValid(['Id' => '']));
        $this->assertFalse($this->form->isValid([]));
    }

    public function testGetQuestion()
    {
        $form = new Application_Form_Confirmation(Licence::class);

        $this->assertEquals('confirmation_question_default', $form->getQuestion());
    }

    public function testSetQuestion()
    {
        $form = new Application_Form_Confirmation(Licence::class);

        $form->setQuestion('Wollen Sie wirklich das Internet löschen?');

        $this->assertEquals('Wollen Sie wirklich das Internet löschen?', $form->getQuestion());
    }

    public function testSetModel()
    {
        $this->form->setModel(Licence::get(2));
        $this->assertEquals(2, $this->form->getModelId());
    }

    public function testSetModelNull()
    {
        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('must be Opus\Model\AbstractDb');
        $this->form->setModel(null);
    }

    public function testSetModelNotObject()
    {
        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('must be Opus\Model\AbstractDb');
        $this->form->setModel('notamodel');
    }

    public function testSetModelBadModel()
    {
        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('not instance of');
        $this->form->setModel(new Date());
    }

    public function testRenderQuestion()
    {
        $this->useEnglish();

        $this->form->setModel(Licence::get(4));

        $this->form->setQuestion('Klasse: %1$s, Name: %2$s');

        $this->assertEquals(
            'Klasse: Licence, Name: <span class="displayname">Creative Commons - CC BY-ND - Namensnennung - Keine Bearbeitungen 4.0 International</span>',
            $this->form->renderQuestion()
        );
    }

    public function testRenderQuestionTranslated()
    {
        $this->useEnglish();

        $this->form->setModel(Licence::get(1));

        $this->form->setQuestion('SignatureValue'); // belieber Schlüssel, es geht nur um die Übersetzung

        $this->assertEquals('Signature', $this->form->renderQuestion());
    }

    public function testRenderQuestionEscaped()
    {
        $licence = Licence::new();

        $licence->setNameLong('<h1>Name mit Tags</h1>');

        $this->form->setModel($licence);

        $this->assertNotContains('<h1>Name mit Tags</h1>', $this->form->renderQuestion());
        $this->assertContains('&lt;h1&gt;Name mit Tags&lt;/h1&gt;', $this->form->renderQuestion());
    }
}
