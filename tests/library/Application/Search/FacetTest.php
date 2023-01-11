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
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_Search_FacetTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'translation'];

    public function testConstructFacet()
    {
        $facet = new Application_Search_Facet('test', [
            'translated'        => 'true',
            'accessResource'    => 'documents',
            'translationPrefix' => 'Test_',
        ]);

        $this->assertEquals('test', $facet->getName());
        $this->assertTrue($facet->isTranslated());
        $this->assertEquals('documents', $facet->getAccessResource());
        $this->assertEquals('Test_', $facet->getTranslationPrefix());

        // TODO extend test
    }

    public function testSetTranslated()
    {
        $facet = new Application_Search_Facet('year');

        $this->assertFalse($facet->isTranslated());

        $facet->setTranslated('1');
        $this->assertTrue($facet->isTranslated());
        $facet->setTranslated('0');
        $this->assertFalse($facet->isTranslated());

        $facet->setTranslated('true');
        $this->assertTrue($facet->isTranslated());
        $facet->setTranslated('false');
        $this->assertFalse($facet->isTranslated());

        $facet->setTranslated(true);
        $this->assertTrue($facet->isTranslated());
        $facet->setTranslated(false);
        $this->assertFalse($facet->isTranslated());
    }

    public function testIsAllowed()
    {
        $this->markTestIncomplete();
    }

    public function testGetLabel()
    {
        $facet = new Application_Search_Facet('subject');

        $this->assertEquals('Bauhaus', $facet->getLabel('Bauhaus'));
    }

    public function testGetLabelTranslated()
    {
        $this->useGerman();

        $facet = new Application_Search_Facet('doctype');
        $facet->setTranslated(true);

        $this->assertEquals('Wissenschaftlicher Artikel', $facet->getLabel('article'));
    }

    public function testGetLabelTranslatedWithPrefix()
    {
        $this->useGerman();

        $facet = new Application_Search_Facet('server_state');
        $facet->setTranslated(true);
        $facet->setTranslationPrefix('Opus_Document_ServerState_Value_');

        $this->assertEquals('Freigegeben', $facet->getLabel('published'));
    }

    public function testGetHeading()
    {
        $facet = new Application_Search_Facet('doctype');

        $this->assertEquals('doctype_facet_heading', $facet->getHeading());
    }

    public function testSetHeading()
    {
        $facet = new Application_Search_Facet('test');

        $facet->setHeading('TestHeading');
        $this->assertEquals('TestHeading', $facet->getHeading());

        $facet->setHeading(null);
        $this->assertEquals('test_facet_heading', $facet->getHeading());
    }

    public function testGetIndexField()
    {
        $facet = new Application_Search_Facet('year');

        $this->assertEquals('year', $facet->getIndexField());

        $facet->setIndexField('year_inverted');

        $this->assertEquals('year_inverted', $facet->getIndexField());
    }

    public function testGetLimit()
    {
        $this->markTestIncomplete();
    }
}
