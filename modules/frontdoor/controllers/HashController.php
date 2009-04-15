<?php
class Frontdoor_HashController extends Zend_Controller_Action
{
    /**
     *
     *getting hashvalues from Opus_Document to display them
     *
     *
     */
     public function indexAction()
    {
       $request = $this->getRequest();
       $docId = $request->getParam('docId');
       $this->view->docId = $docId;
       $document = new Opus_Document($docId);

       $title = $document->getTitleMain('0')->getValue();
       $this->view->title = $title;

       $hash0 = $document->getFile('0')->getHashValue('0')->getValue();
       $hash0_type = $document->getFile('0')->getHashValue('0')->getType();

       $hash1 = $document->getFile('0')->getHashValue('1')->getValue();
       $hash1_type = $document->getFile('0')->getHashValue('1')->getType();

       $this->view->hash0_type = $hash0_type;
       $this->view->hash0 = $hash0;
       $this->view->hash1_type = $hash1_type;
       $this->view->hash1 = $hash1;
    }

}
