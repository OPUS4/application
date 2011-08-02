<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Frontdoor_Model_FrontdoorDeliveryException extends Exception {

    protected $translateKey;

    public function getTranslateKey() {
        return $this->translateKey;
    }
}
?>
