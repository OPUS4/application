<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Frontdoor_Model_DocumentDeletedException extends Frontdoor_Model_FrontdoorDeliveryException {
    public function __construct() {
        $this->translateKey = "frontdoor_document_deleted";
        $this->code = 410;
    }
}
?>
