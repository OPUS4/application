<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class LibraryTests_Form_Validate_RequiredIfValueTest extends PHPUnit_Framework_TestCase {

    private $validator;

    protected function setUp() {
        $this->validator = new Form_Validate_RequiredIf(array(
            'target' => 'Language', 'targetValue' => 'Englisch', 'negate' => false));
    }

    /**
     * Test current field and target field have value.
     */
    public function testRequiredValid() {
        $context = array();
        $context['Language'] = 'Englisch';

        $this->assertTrue($this->validator->isValid('hasValue', $context));
    }

    /**
     * Test current field has no value, target field has value.
     */
    public function testRequiredFailed() {
        $context = array();
        $context['Language'] = 'Englisch';

        $this->assertFalse($this->validator->isValid(null, $context));
    }

    /**
     *
     */
    public function testRequiredValidTargetEmpty() {
        $context = array();
        $context['Language'] = 'Deutsch';

        $this->assertTrue($this->validator->isValid('hasValue', $context));
    }

    /**
     *
     */
    public function testRequiredFailedTargetEmpty() {
        $context = array();
        $context['Language'] = 'Deutsch';

        $this->assertTrue($this->validator->isValid(null, $context));
    }

}

?>
