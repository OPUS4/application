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
 * @package     Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * @covers Admin_PersonController
 */
class PersonControllerTest extends ControllerTestCase {

    private $documentId;

    public function tearDown() {
        $this->removeDocument($this->documentId);

        parent::tearDown();
    }

    public function testAssignAction() {
        $this->dispatch('/admin/person/assign/document/146');

        $this->validateXHTML();
        $this->verifyBreadcrumbDefined();

        $this->assertXpath('//option[@value="author" and @selected="selected"]'); // default
    }

    public function testAssignActionBadDocumentId() {
        $this->dispatch('/admin/person/assign/document/bad');
        $this->assertRedirectTo('/admin/documents');
        $this->verifyFlashMessage('admin_document_error_novalidid');
    }

    public function testAssignActionUnknownDocumentId() {
        $this->dispatch('/admin/person/assign/document/5555');
        $this->assertRedirectTo('/admin/documents');
        $this->verifyFlashMessage('admin_document_error_novalidid');
    }

    public function testAssignActionNoDocumentId() {
        $this->dispatch('/admin/person/assign');
        $this->assertRedirectTo('/admin/documents');
        $this->verifyFlashMessage('admin_document_error_novalidid');
    }

    public function testAssignActionWithRoleParam() {
        $this->dispatch('/admin/person/assign/document/146/role/translator');

        $this->assertNotXpath('//option[@value="author" and @selected="selected"]');
        $this->assertXpath('//option[@value="translator" and @selected="selected"]');
    }

    public function testAssignActionCancel() {
        $this->getRequest()->setMethod('POST')->setPost(array(
            'Cancel' => 'Abbrechen'
        ));

        $this->dispatch('/admin/person/assign/document/146/role/advisor');
        $this->assertRedirectTo('/admin/document/edit/id/146/continue/addperson');
    }

    public function testAssignActionAddPerson() {
        $document = $this->createTestDocument();

        $this->documentId = $document->store();

        $this->getRequest()->setMethod('POST')->setPost(array(
            'LastName' => 'Testy-AssignAction',
            'Document' => array(
                'Role' => 'translator'
            ),
            'Save' => 'Speichern'
        ));

        $this->dispatch('/admin/person/assign/document/' . $this->documentId . '/role/translator');

        $location = $this->getLocation();

        $matches = array();
        preg_match('/person\/(\d+)\//', $location, $matches);
        $personId = $matches[1];

        $person = new Opus_Person($personId);

        $lastName = $person->getLastName();

        $person->delete();

        $this->assertTrue(strpos($location, '/admin/document/edit/id/'
                . $this->documentId . '/continue/addperson') === 0);

        $this->assertEquals('Testy-AssignAction', $lastName);
    }

    public function testEditlinkedAction() {
        $this->dispatch('/admin/person/editlinked/document/146/personId/259');

        $this->validateXHTML();
        $this->verifyBreadcrumbDefined();

        $this->assertXpath('//input[@id="LastName" and @value="Doe"]');
        $this->assertXpath('//input[@id="FirstName" and @value="John"]');
    }

    public function testEditlinkedActionBadDocumentId() {
        $this->dispatch('/admin/person/editlinked/document/bad');
        $this->assertRedirectTo('/admin/documents');
        $this->verifyFlashMessage('admin_document_error_novalidid');
    }

    public function testEditlinkedActionUnknownDocumentId() {
        $this->dispatch('/admin/person/editlinked/document/5555');
        $this->assertRedirectTo('/admin/documents');
        $this->verifyFlashMessage('admin_document_error_novalidid');
    }

    public function testEditlinkedActionNoDocumentId() {
        $this->dispatch('/admin/person/editlinked');
        $this->assertRedirectTo('/admin/documents');
        $this->verifyFlashMessage('admin_document_error_novalidid');
    }

    public function testEditlinkedActionNoPersonId() {
        $this->dispatch('/admin/person/editlinked/document/146');
        $this->assertRedirectTo('/admin/document/edit/id/146/continue/true');
    }

    public function testEditlinkedActionUnknownPersonId() {
        $this->dispatch('/admin/person/editlinked/document/146/personId/7777');
        $this->assertRedirectTo('/admin/document/edit/id/146/continue/true');
    }

    public function testEditlinkedActionBadPersonId() {
        $this->dispatch('/admin/person/editlinked/document/146/personId/bad');
        $this->assertRedirectTo('/admin/document/edit/id/146/continue/true');
    }

    public function testEditlinkedActionPersonNotLinkedToDocument() {
        $this->markTestIncomplete('Klären wofür die Action verwendet werden soll.');
        $this->dispatch('/admin/person/editlinked/document/146/personId/253');
        $this->assertRedirectTo('/admin/document/edit/id/146/continue/true');
    }

    public function testEditlinkedActionCancel() {
        $this->getRequest()->setMethod('POST')->setPost(array(
            'Cancel' => 'Abbrechen'
        ));

        $this->dispatch('/admin/person/editlinked/document/146/personId/259');
        $this->assertRedirectTo('/admin/document/edit/id/146/continue/true');
    }

    public function testEditlinkedActionSave() {
        $document = $this->createTestDocument();

        $person = new Opus_Person();
        $person->setLastName('Testy-EditlinkedAction');

        $person = $document->addPersonTranslator($person);

        $this->documentId = $document->store();

        $personId = $person->getModel()->getId();

        $this->getRequest()->setMethod('POST')->setPost(array(
            'PersonId' => $personId,
            'LastName' => 'Testy',
            'FirstName' => 'Simone',
            'Document' => array(
                'Role' => 'translator'
            ),
            'Save' => 'Speichern'
        ));

        $this->dispatch('/admin/person/editlinked/personId/' . $personId . '/role/translator/document/'
            . $this->documentId);

        $this->assertRedirectTo('/admin/document/edit/id/' . $this->documentId
            . '/continue/updateperson/person/' . $personId);

        $person = new Opus_Person($personId);

        $this->assertEquals('Testy', $person->getLastName());
        $this->assertEquals('Simone', $person->getFirstName());
    }

    public function testIndex()
    {
        $this->dispatch('/admin/person');

        $this->assertResponseCode(200);
        $this->validateXHTML();
    }

    public function testIndexDefaults()
    {
        $this->dispatch('/admin/person');

        $this->assertResponseCode(200);
        $this->assertQueryCount('td.lastname', 50);
    }

    public function testIndexLimit()
    {
        $this->dispatch('/admin/person/index/limit/20');

        $this->assertResponseCode(200);
        $this->assertQueryCount('td.lastname', 20);
    }

    /**
     * TODO cannot detect present but empty parameters, something like ".../role/" or ".../role//filter/en"
     */
    public function redirectProvider()
    {
        return array(
            'limit zero' => ['limit/0', '/admin/person'],
            'limit not integer' => ['limit/infinity', '/admin/person'],
            // 'limit empty' => ['limit/', '/admin/person'],
            'keep good parameters' => ['page/2/limit/-5', '/admin/person/index/page/2'],
            // 'role empty' => ['role/', '/admin/person'],
            'role invalid' => ['role/unknown', '/admin/person'],
            'page invalid' => ['page/here', '/admin/person'],
            'page zero' => ['page/0', '/admin/person'],
            'page negative' => ['page/-1', '/admin/person']
        );
    }

    /**
     * @param $dispatchUrl
     * @param $redirectUrl
     *
     * @dataProvider redirectProvider
     */
    public function testRedirectForBadParameters($urlParams, $redirectUrl)
    {
        $this->dispatch("/admin/person/index/$urlParams");

        $this->assertResponseCode(302);
        $this->assertRedirectTo($redirectUrl);
    }

    public function testIndexFilter()
    {
        $this->dispatch('/admin/person/index/filter/Walruss');

        $this->assertResponseCode(200);
        $this->assertQueryCount(1, 'td.lastname');
        $this->assertQueryContentContains('td.lastname', 'Walruss');
        $this->assertQueryContentContains('td.firstname', 'Wally');
        $this->assertQueryContentContains('td.documents', 8);
    }

    public function testIndexFilterCaseInsensitive()
    {
        $this->dispatch('/admin/person/index/filter/wAlRuSs');

        $this->assertResponseCode(200);
        $this->assertQueryCount(1, 'td.lastname');
        $this->assertNotQueryContentContains('td.lastname', 'wAlRuSs');
        $this->assertQueryContentContains('td.lastname', 'Walruss');
        $this->assertQueryContentContains('td.firstname', 'Wally');
        $this->assertQueryContentContains('td.documents', 8);
    }

    public function testIndexFilterHighlight()
    {
        $this->dispatch('/admin/person/index/filter/Walruss');

        $this->assertResponseCode(200);
        $this->assertQueryContentContains('td.lastname', '<b>Walruss</b>');
    }

    public function testIndexDocumentsLink()
    {
        $this->dispatch('/admin/person/index/filter/Walruss');

        $this->assertResponseCode(200);
        $this->assertQueryContentContains('td.documents', 8);
    }

    public function testIndexPersonRoles()
    {
        $this->dispatch('/admin/person/index/filter/Walruss');

        $this->assertResponseCode(200);
        $this->assertQueryContentContains('td.roles', 'author');
        $this->assertQueryContentContains('td.roles', '8');
    }

    public function testIndexFilterByRole()
    {
        $this->dispatch('/admin/person/index/role/author/limit/50');

        $this->assertResponseCode(200);

        $this->assertQueryCount('a.author', 50);

        $this->markTestIncomplete('more testing');
    }

    public function testIndexFilterByRoleCaseInsensitive()
    {
        $this->dispatch('/admin/person/index/role/AuTHor/limit/50');

        $this->assertResponseCode(200);

        $this->assertQueryCount('a.author', 50);
    }

    public function testIndexRedirectPost()
    {
        $this->getRequest()->setPost(array(
            'filter' => 'en', 'role' => 'author', 'limit' => '10',
        ))->setMethod('POST');

        $this->dispatch('/admin/person');

        $this->assertResponseCode(302);
        $this->assertRedirectTo('/admin/person/index/role/author/limit/10/filter/en');
    }

    public function testIndexPaginationFirstPage()
    {
        $this->dispatch('/admin/person/index/page/1/limit/10');

        $this->assertResponseCode(200);
        $this->assertQueryCount('td.lastname', 10);
        $this->assertQuery('div.pagination-first');
        $this->assertQuery('div.pagination-prev');
        $this->assertQuery('a.pagination-next');
        $this->assertQuery('a.pagination-last');
        $this->assertQueryContentContains('li.currentPage', 1);
        $this->assertNotQuery('li.currentPage/a'); // no link for current page

        $this->assertQueryContentContains('li/a', 10); // might fail if max number of visible pages is smaller than 10
        $this->assertQueryCount('ul.paginationControl/li', 14);
    }

    public function testIndexPaginationLastPage()
    {
        // use 5 because it is larger than max page number (with current test data)
        $this->dispatch('/admin/person/index/page/5/limit/50');

        $this->assertResponseCode(200);
        $this->assertQuery('a.pagination-first');
        $this->assertQuery('a.pagination-prev');
        $this->assertQuery('div.pagination-next');
        $this->assertQuery('div.pagination-last');

        $this->assertQueryContentContains('li.currentPage', 3); // with current test data
        $this->assertNotQuery('li.currentPage/a'); // no link for current page
        $this->assertQueryContentContains('li/a', 1);
        $this->assertQueryContentContains('li/a', 2);
        $this->assertQueryCount('ul.paginationControl/li', 7); // 3 pages + 4 nav links
    }

    public function testIndexPagination()
    {
        $this->dispatch('/admin/person/index/page/10/limit/5');

        $this->assertResponseCode(200);
        $this->assertQuery('a.pagination-first');
        $this->assertQuery('a.pagination-prev');
        $this->assertQuery('a.pagination-next');
        $this->assertQuery('a.pagination-last');

        $this->assertQueryContentContains('li.currentPage', 10);
        $this->assertNotQuery('li.currentPage/a');

        $this->assertQueryCount('ul.paginationControl/li', 14);

        // links for pages 6 - 15 should exist
        $this->assertNotXpath('//li/a[@href = "/admin/person/index/page/5/limit/5"]');
        $this->assertXpath('//li/a[@href = "/admin/person/index/page/6/limit/5"]');
        $this->assertXpathContentContains('//li/a[@href = "/admin/person/index/page/6/limit/5"]', 6);
        $this->assertXpath('//li/a[@href = "/admin/person/index/page/15/limit/5"]');
        $this->assertXpathContentContains('//li/a[@href = "/admin/person/index/page/15/limit/5"]', 15);
        $this->assertNotXpath('//li/a[@href = "/admin/person/index/page/16/limit/5"]');
    }

    public function testIndexPaginationPosition()
    {
        $this->dispatch('/admin/person/index/limit/20');

        $this->assertResponseCode(200);
        $this->assertQueryContentContains('div.results_pagination/div', '1 - 20');

        $this->resetRequest();

        $this->dispatch('/admin/person/index/page/2/limit/20');

        $this->assertResponseCode(200);
        $this->assertQueryContentContains('div.results_pagination/div', '21 - 40');
    }

    public function testIndexShowLastPageForPageParameterTooLarge()
    {
        $this->dispatch('/admin/person/index/page/1000');

        $this->assertResponseCode(200);

        // last page should be displayed
        $this->assertQuery('div.pagination-next');
        $this->assertQuery('div.pagination-last');
    }

    public function testIndexPageDoNotShowPaginationIfResultIsSmallerThanLimit() {
        $this->dispatch('/admin/person/index/filter/wally/page/1000/limit/1');

        $this->assertResponseCode(200);
        $this->assertNotQuery('ul.paginationControl');

        $this->resetRequest();

        $this->dispatch('/admin/person/index/filter/en/page/100');

        $this->assertResponseCode(200);
        $this->assertNotQuery('ul.paginationControl');
    }

    public function testAdminMenuEntry()
    {
        $this->useEnglish();

        $this->dispatch('/admin');

        $this->assertResponseCode(200);
        $this->assertQueryContentContains('li.group-sky/a/strong', 'Persons');
        $this->assertXpath('//li/a[@href = "/admin/person"]');
    }

    public function testAccessControl()
    {
        $this->useEnglish();
        $this->enableSecurity();
        $this->loginUser('admin', 'adminadmin');

        $this->dispatch('/admin');

        $this->assertResponseCode(200);
        $this->assertQueryContentContains('li.group-sky/a/strong', 'Persons');
        $this->assertXpath('//li/a[@href = "/admin/person"]');

        $this->loginUser('security2', 'security2pwd');

        $this->dispatch('/admin');

        $this->assertResponseCode(200);
        $this->assertQueryContentContains('li.inactive/strong', 'Persons');
    }

    public function testAccessControlForPersonsResource()
    {
        // TODO create helper class for creating test accounts for general use in test (reduce fixed testdata)
        $this->markTestIncomplete('create test account on the fly with access to persons resource');
    }

    public function testDisplaySelectUpdateErrorMessage()
    {
        $this->useEnglish();

        $this->getRequest()->setMethod('POST')->setPost(array(
            'LastName' => 'Test',
            'DateOfBirth' => '1970-01-01',
            'Save' => 'Weiter'
        ));

        $this->dispatch('/admin/person/edit/last_name/Author/first_name/One');

        $this->assertQueryContentContains('ul.form-errors', 'at least one field');
        $this->assertNotQueryContentContains('ul.form-errors', 'Date of Birth');

    }

}