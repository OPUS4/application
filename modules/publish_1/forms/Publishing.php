<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Publishing
 *
 * @author Susanne Gottwald
 */
class Publishing extends Zend_Form{

    public function init() {

        $config = Zend_Registry::get('Zend_Config');
        
       //Select with different document types given by the used function
        $listOptions = $this->getXmlDocTypeFiles();
        $doctypes = $this->createElement('select', 'Type');
        $doctypes->setLabel('selecttype')
                ->setMultiOptions(array_merge(array('' => 'choose_valid_doctype'), $listOptions))
                ->setRequired(true);

        //Title of the document
        //extern
        $title = $this->createElement('text', 'TitleMain');
        $title->addValidator('NotEmpty')
                ->setLabel('Title')
                ->setRequired(true);

        //Author first name
        //extern
        $authorfirst = $this->createElement('text', 'AuthorFirstName');
        $authorfirst->addValidator('NotEmpty')
                ->setLabel('Authorfirstname')
                ->setRequired(true);

        //Author last name
        //extern
        $authorlast = $this->createElement('text', 'AuthorLastName');
        $authorlast->addValidator('NotEmpty')
                ->setLabel('Authorlastname')
                ->setRequired(true);

        //Publishing year
        //==>INTERNAL!!!
        $year = $this->createElement('text', 'PublishedYear');
        $year->addValidator('Digits')
                ->addValidator('StringLength', false, array(4))
                ->setRequired(true)
                ->setLabel('Year');

        //Abstract
        //extern
        $abstract = $this->createElement('textarea', 'TitleAbstract', array('rows'=>6, 'cols'=>40));
        $abstract->addValidator('NotEmpty')
                ->setLabel('Abstract')
                ->setRequired(true)
                ->addFilter('StripTags');

        // get path to store files
        $tempPath = $config->path->workspace->temp;
        if (true === empty($tempPath)) {
            $tempPath = '../workspace/tmp/';
        }

        // get allowed filetypes
        @$filetypes = $config->publish->filetypes->allowed;
        if (true === empty($filetypes)) {
            $filetypes = 'pdf,txt,html,htm';
        }

        $fileupload = $this->createElement('File', 'fileupload');
        $fileupload->setLabel('fileupload')
                ->setRequired(true)
                ->setDestination($tempPath)
                ->addValidator('Count', false, 1)     // ensure only 1 file
                ->addValidator('Size', false, 1024000) // limit to 1000K
                ->addValidator('Extension', false, $filetypes); // allowed filetypes by extension

        //Submit button
        $submit = $this->createElement('submit', 'send');
        $submit->setLabel('Send');

        //add all elements to the form
        $this->addElement($doctypes)
                ->addElement($title)
                ->addElement($authorfirst)
                ->addElement($authorlast)
                ->addElement($year)
                ->addElement($abstract)
                ->addElement($fileupload)
                ->addElement($submit);
    }

    /**
     * OLD function getXmlDocFiles, TODO: really needed? or other way of getting the types?
     * @return array() of found docTypes
     */
     protected function getXmlDocTypeFiles() {
        // TODO Do not use a hardcoded path to xml files
        $xml_path = "../config/xmldoctypes/";
        $result = array();
        if ($dirhandle = opendir($xml_path)) {
            while (false !== ($file = readdir($dirhandle))) {
                if (preg_match("/.xml$/", $file) === 0) {
                    continue;
                }

                $path_parts = pathinfo($file);
                $filename = $path_parts['filename'];
                $basename = $path_parts['basename'];
                $extension = $path_parts['extension'];
                if (($basename === '.') or ($basename === '..') or ($extension !== 'xml')) {
                    continue;
                }
                $result[$filename] = $filename;
            }
            closedir($dirhandle);
            asort($result);
        }
        return $result;
    }
}
?>
