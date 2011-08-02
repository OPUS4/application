<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Frontdoor_Model_FileAccessNotAllowedException extends Frontdoor_Model_FrontdoorDeliveryException {
    public function __construct() {
        $this->translateKey = 'frontdoor_no_file_access';
        $this->code = 403;
    }
}
?>
