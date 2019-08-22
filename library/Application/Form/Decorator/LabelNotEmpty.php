<?php

class Application_Form_Decorator_LabelNotEmpty extends Zend_Form_Decorator_Label
{

    public function render($content)
    {
        $label = $this->getElement()->getLabel();

        if (! is_null($label) && trim($label) !== 0) {
            return parent::render($content);
        } else {
            return $content;
        }
    }
}
