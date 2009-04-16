<?php
class Frontdoor_HashController extends Zend_Controller_Action
{
    /**
     *
     *getting hashvalues from Opus_Document to display them
     *case: only one file exists
     *generalisation for any filenumbers has to be done
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

       if (is_array ($files = $document->getFile()) === true)
       {
          $filenumber = count($files);
          $this->view->filenumber = $filenumber;
          $first_hash = $document->getFile('0')->getHashValue('0')->getValue();
          $this->view->first_hash = $first_hash;
          $hash_values = array();
          $hash_types = array();
/*
 * Iteration over all files and hashvalues (not completed)
 *

          if ($first_hash !== NULL)
          {
             for ($i = 0; $i < $fileNumber;)
             {
                 for ($j = 0; $j < 2; $i++)
                 {
                    $hash = $document->getFile($i)->getHashValue($j)->getValue();
                    $type = $document->getFile($i)->getHashValue($j)->getType();

                    $hash_values[$hash];
                    $hash_types[$type];
                 }
             }
          }
*/
       }

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
