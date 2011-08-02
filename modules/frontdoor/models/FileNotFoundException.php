<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Frontdoor_Model_FileNotFoundException extends Frontdoor_Model_FrontdoorDeliveryException {
    public function  __construct() {
        $this->translateKey = 'frontdoor_file_not_found';
        $this->code = 404;
    }
}
?>
