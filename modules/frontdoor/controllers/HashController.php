<?php
class Frontdoor_HashController extends Zend_Controller_Action
{
    /**
     *
     *getting hashvalues from Opus_Document to display them
     *case of multiple files and multiple hashtypes exists
     *commits array 'hashLabel' with all filelabels
     *commits array 'hashValueType' with all hashvalues and hashtypes
     *
     */

    public function indexAction()
    {
       $request = $this->getRequest();
       $docId = $request->getParam('docId');
       $this->view->docId = $docId;
       $document = new Opus_Document($docId);
       $doc_data = $document->toArray();
       $this->view->document = $document;
       $this->view->doc_data = $doc_data;
       $title = $document->getTitleMain('0')->getValue();
       $this->view->title = $title;
       $author_exists = $document->getPersonAuthor();
       if (empty ($author_exists))
       {
           $this->author = $author = null;
           $this->view->author = $author;
       }
       else
       {
           $author = $document->getPersonAuthor('0')->getName();
           $this->view->author = $author;
       }

       if (is_array ($files = $document->getFile()) === true)
       {
          $fileNumber = count($files);
          $this->view->fileNumber = $fileNumber;
          $hash_exists = $document->getFile('0')->getHashValue();
          if (empty ($hash_exists))
          {
             $this->first_hash = $first_hash = null;
             $this->view->first_hash = $first_hash;
          }
          else
          {
            $first_hash = $document->getFile('0')->getHashValue('0')->getValue();
            $this->view->first_hash = $first_hash;
          }
          $this->view->first_hash = $first_hash;
          $hashValueType = array();
          $hashLabel = array();

          // Iteration over all files, hashtypes and -values

          if ($first_hash !== NULL)
          {
             for ($i = 0; $i < $fileNumber; $i++)
             {
                 if (is_array ($hashes = $document->getFile($i)->getHashValue()) === true)
                 {
                     $hashNumber = count($hashes);
                     $label = $document->getFile($i)->getLabel();
                     $hashLabel[] = $label;

                     for ($j = 0; $j < $hashNumber; $j++)
                     {
                        $hashValue = $document->getFile($i)->getHashValue($j)->getValue();
                        $hashType = $document->getFile($i)->getHashValue($j)->getType();
                        $hashValueType['hashValue_' .$i. '_' .$j] = $hashValue;
                        $hashValueType['hashType_' .$i. '_' .$j] = $hashType;

                     }
                  }
              }
          }
       }
       $this->view->hashValueType = $hashValueType;
       $this->view->hashLabel = $hashLabel;
    }

    public function verifyAction()
    {
       $docId = $this->getRequest()->getParam('docId');
       $doc = new Opus_Document($docId);
       $gpg = new Opus_GPG();

       foreach ($doc->getFile() as $file)
       {
          try
          {
             $this->view->verifyResult[$file->getPathName()] = $gpg->verifyPublicationFile($file);
          }
          catch (Exception $e)
          {
             $this->view->verifyResult[$file->getPathName()] = array(array($e->getMessage()));
          }
       }
       print_r($verifyResult);
    }
}
