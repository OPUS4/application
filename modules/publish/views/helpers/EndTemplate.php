<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EndTemplate
 *
 * @author Susanne Gottwald
 */
class View_Helper_EndTemplate extends Zend_View_Helper_Abstract{

    public function endTemplate(Publish_Form_PublishingSecond $form) {
        $log = Zend_Registry::get('Zend_Log');
        $session = new Zend_Session_Namespace('Publish');
        $elementCount = $session->elementCount;
        $log->debug("Template expects " . $elementCount . " elements and group.");

        $formCount = 0;
        $elements = $form->getElements();
        foreach ($elements AS $element) {
            $log->debug("Form element: " . $element->getName());
        }
        $numberOfElements = count($elements);
        $log->debug("Number of elements in current form = " . $numberOfElements);
        $groups = $form->getDisplayGroups();
        $formCount = count($groups);
        $log->debug("Number of groups in current form = " . $formCount);
        $groupCount = 0;

        foreach ($groups AS $group) {
            $log->debug("Group: " . $group->getName());
            $groupElements = $group->getElements();
            $groupCount = $groupCount + count($groupElements);
        }
        $log->debug("Number of elements in these groups " . $groupCount);
        $formCount = $formCount + $numberOfElements - $groupCount;

        $log->debug("Form expects " . $formCount. " elements and group.");

        if ($formCount > $elementCount)
            return "Fehler: es fehlen Dokumenttyp-Felder innerhalb des Templates. Bitte vergleichen und ergänzen Sie. ";
        else
            if ($formCount < $elementCount)
                return "Fehler: es gibt zu viele Dokumenttyp-Felder innerhalb des Templates. Bitte vergleichen und löschen Sie.";
    }
}
?>
