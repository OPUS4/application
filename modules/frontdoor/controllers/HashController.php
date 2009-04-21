<?php
class Frontdoor_HashController extends Zend_Controller_Action
{
    /**
     *
     *getting hashvalues from Opus_Document to display them
     *case: only one file exists (preliminary)
     *generalisation for multiple files and hashtypes has been done here by iteration
     *over all files, hashtypes and -values
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
          $fileNumber = count($files);
          $this->view->filenumber = $fileNumber;
          $first_hash = $document->getFile('0')->getHashValue('0')->getValue();
          $this->view->first_hash = $first_hash;
          $hash_values = array();
          $hash_types = array();
          $hash_labels = array();
          $hashOut = array();

// Iteration over all files, hashtypes and -values

          if ($first_hash !== NULL)
          {
             for ($i = 0; $i < $fileNumber; $i++)
             {
                 if (is_array ($hashes = $document->getFile($i)->getHashValue()) === true)
                 {
                     $hashNumber = count($hashes);
                     $label = $document->getFile($i)->getLabel();
                     $hash_labels[] = $label;

                     for ($j = 0; $j < $hashNumber; $j++)
                     {
                        $hash = $document->getFile($i)->getHashValue($j)->getValue();
                        $type = $document->getFile($i)->getHashValue($j)->getType();
                        $hash_values[] = $hash;
                        $hash_types[] = $type;
                     }
                  }
              }
          }
       }
       $this->view->hash_labels = $hash_labels;
       if ($fileNumber = $hashNumber)
       {
           $hashOut = array_combine ($hash_values, $hash_types);
           $this->view->hashOut = $hashOut;
       }

//getting hashvalues from first file (has to be replaced by iteration above, when view is adjusted)

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
