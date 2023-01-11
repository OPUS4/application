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

use Opus\Common\File;

class Application_Form_Decorator_FileHashTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['view', 'translation'];

    public function testRenderWithoutElement()
    {
        $decorator = new Application_Form_Decorator_FileHash();

        $this->assertEquals('content', $decorator->render('content'));
    }

    public function testRenderWithWrongElement()
    {
        $decorator = new Application_Form_Decorator_FileHash();

        $decorator->setElement(new Zend_Form_Element_Text('text'));

        $this->assertEquals('content', $decorator->render('content'));
    }

    public function testRender()
    {
        $element = new Application_Form_Element_FileHash('name');

        $file   = File::get(116);
        $hashes = $file->getHashValue();
        $hash   = $hashes[0];

        $this->assertEquals('MD5', $hash->getType());

        $element->setValue($hash);
        $element->setFile($file);

        $decorator = new Application_Form_Decorator_FileHash();

        $decorator->setElement($element);

        $output = $decorator->render('content');

        $this->assertEquals(
            'content'
            . '<div class="textarea hashsoll">1ba50dc8abc619cea3ba39f77c75c0fe</div>'
            . '<input type="hidden" name="name[Soll]" value="1ba50dc8abc619cea3ba39f77c75c0fe" id="name-Soll" />',
            $output
        );
    }

    public function testRenderWithIst()
    {
        $this->useEnglish();
        $element = new Application_Form_Element_FileHash('name');

        $file   = File::get(116);
        $hashes = $file->getHashValue();
        $hash   = $hashes[0];

        $this->assertEquals('MD5', $hash->getType());

        $hash->setValue('1ba50dc8abc619cea3ba39f77c75c0ff'); // Abweichung provozieren

        $element->setValue($hash);
        $element->setFile($file);

        $decorator = new Application_Form_Decorator_FileHash();

        $decorator->setElement($element);

        $output = $decorator->render('content');

        $this->assertEquals('content'
            . '<div class="textarea hashsoll"><span class="hash-label">Expected:</span>1ba50dc8abc619cea3ba39f77c75c0ff</div>'
            . '<input type="hidden" name="name[Soll]" value="1ba50dc8abc619cea3ba39f77c75c0ff" id="name-Soll" />'
            . '<div class="textarea hashist"><span class="hash-label">Actual:</span>1ba50dc8abc619cea3ba39f77c75c0fe</div>'
            . '<input type="hidden" name="name[Ist]" value="1ba50dc8abc619cea3ba39f77c75c0fe" id="name-Ist" />', $output);
    }

    public function testRenderWithMissingFile()
    {
        $this->useEnglish();
        $element = new Application_Form_Element_FileHash('name');

        $file   = File::get(123);
        $hashes = $file->getHashValue();

        $hash = $hashes[0];

        $element->setValue($hash);
        $element->setFile($file);

        $decorator = new Application_Form_Decorator_FileHash();

        $decorator->setElement($element);

        $output = $decorator->render('content');

        $this->assertEquals('content'
            . '<div class="textarea hashsoll"><span class="hash-label">Expected:</span>1ba50dc8abc619cea3ba39f77c75c0fe</div>'
            . '<input type="hidden" name="name[Soll]" value="1ba50dc8abc619cea3ba39f77c75c0fe" id="name-Soll" />'
            . '<div class="textarea hashist"><span class="hash-label">Actual:</span>'
            . Application_Translate::getInstance()->translate('frontdoor_checksum_not_verified')
            . '</div>', $output);
    }

    public function testRenderWithFileTooBig()
    {
        $this->useEnglish();
        $config = $this->getConfig();
        $config->merge(new Zend_Config(['checksum' => ['maxVerificationSize' => '0']]));

        $element = new Application_Form_Element_FileHash('name');

        $file   = File::get(116);
        $hashes = $file->getHashValue();

        $hash = $hashes[0];

        $element->setValue($hash);
        $element->setFile($file);

        $decorator = new Application_Form_Decorator_FileHash();

        $decorator->setElement($element);

        $output = $decorator->render('content');

        $this->assertEquals('content'
            . '<div class="textarea hashsoll"><span class="hash-label">Expected:</span>1ba50dc8abc619cea3ba39f77c75c0fe</div>'
            . '<input type="hidden" name="name[Soll]" value="1ba50dc8abc619cea3ba39f77c75c0fe" id="name-Soll" />'
            . '<div class="textarea hashist"><span class="hash-label">Actual:</span>'
            . Application_Translate::getInstance()->translate('frontdoor_file_too_big')
            . '</div>', $output);
    }
}
