<?PHP 

/**
 * 
 */
class Form_Decorator_HtmlTagWithId extends Zend_Form_Decorator_HtmlTag {
    
    protected function _htmlAttribs(array $attribs) {
        if (!is_null($attribs) && isset($attribs['class'])) {
            $attribs['class'] = $attribs['class'] . ' ' . $this->getElement()->getName() . '-data';
        }
        else {
            $attribs = array();
            $attribs['class'] = $this->getElement()->getName() . '-data';
        }
        
        return parent::_htmlAttribs($attribs);;
    }
    
}
