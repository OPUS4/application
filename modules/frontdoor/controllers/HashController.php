<?php
class Frontdoor_HashController extends Zend_Controller_Action
{
    /**
     * Do nothing
     *
     * @return void
     *
     */
     public function indexAction()
    {
       $request = $this->getRequest();
       $docId = $request->getParam('docId');
       $this->view->docId = $docId;
       $document = new Opus_Document($docId);
    }

}
