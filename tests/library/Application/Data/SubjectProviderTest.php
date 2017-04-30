<?php
/**
 * Created by IntelliJ IDEA.
 * User: jens
 * Date: 07.11.16
 * Time: 17:59
 */

class Application_Data_SubjectProviderTest extends ControllerTestCase {

    public function testGetValues() {
        $provider = new Application_Data_SubjectProvider();

        $data = $provider->getValues('sch');

        $this->assertInternalType('array', $data);
        $this->assertCount(20, $data);
    }

}