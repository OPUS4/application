<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 3/26/14
 * Time: 11:53 AM
 * To change this template use File | Settings | File Templates.
 */

class Rss_ErrorController extends Controller_Action {

    public function errorAction() {
        // 404 error -- controller or action not found
        $this->getResponse()->setHttpResponseCode(404);
        $this->view->title = 'error_page_not_found';
        $this->view->message = $this->view->translate('error_page_not_found');
        $this->view->errorMessage = $this->view->translate('error_search_unavailable');
    }

}