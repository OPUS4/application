<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 3/20/14
 * Time: 2:52 PM
 * To change this template use File | Settings | File Templates.
 */

class RefereeTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->enableSecurity();
        $this->loginUser('referee', 'refereereferee');
    }

    public function tearDown() {
        $this->logoutUser();
        $this->restoreSecuritySetting();
        parent::tearDown();
    }


    public function testAccessReviewModule() {
        $this->useEnglish();
        $this->dispatch('/review');
        $this->assertQueryContentContains('//html/head/title', 'Review Documents');
        $this->assertQueryContentContains('//html/body', 'Review Documents');
    }

    public function testPublishDocument() {
        // TODO
    }

}
