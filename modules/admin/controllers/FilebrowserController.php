<?php

/**
 * Browsing of file import folder for adding files to documents.
 *
 */
class Admin_FilebrowserController extends Controller_Action {

    /**
     * Shows files in import folder.
     */
    public function indexAction() {
        $importHelper = new Admin_Model_FileImport();

        $files = $importHelper->listFiles();
        
        $this->view->files = $files;
    }

    /**
     * Imports file(s) from import folder for document.
     */
    public function importAction() {
    }

}

?>
