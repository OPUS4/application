<?php

class Admin_Model_FileImport {

    private $__importFolder = 'TODO get from config/or whatever';

    /**
     * Lists files in import folder.
     */
    public function listFiles() {
        $files = Controller_Helper_Files::listFiles($this->__importFolder);

        return $files;
    }

}

?>
