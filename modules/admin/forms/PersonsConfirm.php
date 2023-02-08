<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Model\ModelInterface;
use Opus\Common\Person;

/**
 * TODO use Application_Form_Abstract (actions group is missing in that case - move up?)
 */
class Admin_Form_PersonsConfirm extends Application_Form_Model_Abstract
{
    public const ELEMENT_BACK = 'Back';

    public const SUBFORM_CHANGES = 'Changes';

    public const SUBFORM_DOCUMENTS = 'Documents';

    public const RESULT_BACK = 'Back';

    public const ELEMENT_FORM_ID = 'FormId';

    public function init()
    {
        parent::init();

        $this->removeElement(self::ELEMENT_MODEL_ID);

        $this->addDecorator('FormHelp', ['message' => 'admin_person_edit_confirm_help']);

        $changes = new Admin_Form_Person_Changes();
        $this->addSubForm($changes, self::SUBFORM_CHANGES);

        $documents = new Admin_Form_Person_Documents();
        $documents->addDecorator('fieldset', ['legend' => 'admin_title_documents']);
        // TODO add decorator for hint
        $this->addSubForm($documents, self::SUBFORM_DOCUMENTS);

        // TODO render back button in button area on the left side
        $back = $this->createElement('submit', self::ELEMENT_BACK, [
            'decorators' => [
                'ViewHelper',
                [['liWrapper' => 'HtmlTag'], ['tag' => 'li', 'class' => 'back-element']],
            ],
        ]);
        $this->addElement($back);

        $actions  = $this->getDisplayGroup('actions');
        $elements = $actions->getElements();

        $actions->removeElement(self::ELEMENT_SAVE);
        $actions->removeElement(self::ELEMENT_CANCEL);

        // reordering is necessary to show first 'Save' and then 'Cancel' with 'float: right'
        // 'back' button is 'float: left'
        // TODO maybe use 'float: right' by everywhere
        $actions->addElements([$back, $elements[self::ELEMENT_CANCEL], $elements[self::ELEMENT_SAVE]]);

        $this->setAttrib('class', 'persons-confirm');

        $formId = $this->createElement('hidden', self::ELEMENT_FORM_ID);
        $formId->setValue(uniqid());
        $this->addElement($formId);
    }

    /**
     * @param array $oldValues
     */
    public function setOldValues($oldValues)
    {
        $this->getSubForm(self::SUBFORM_CHANGES)->setOldValues($oldValues);
    }

    /**
     * @param array $person
     * @throws Zend_Form_Exception
     */
    public function populateFromModel($person)
    {
        $persons = Person::getModelRepository();

        $documentIds = $persons->getPersonDocuments($person);

        $docCount = count($documentIds);

        $subform = $this->getSubForm(self::SUBFORM_DOCUMENTS);
        $subform->setDocuments($documentIds, $person);
        $subform->removeDecorator('fieldset');
        $subform->addDecorator('fieldset', [
            'legend' => $this->getTranslator()->translate('admin_title_documents') . " ($docCount)",
        ]);
    }

    /**
     * @param array $changes
     */
    public function setChanges($changes)
    {
        $this->getSubForm(self::SUBFORM_CHANGES)->setChanges($changes);
    }

    /**
     * @return int[]
     */
    public function getDocuments()
    {
        return $this->getSubForm(self::SUBFORM_DOCUMENTS)->getSelectedDocuments();
    }

    /**
     * @param ModelInterface $model
     */
    public function updateModel($model)
    {
        // TODO: Implement updateModel() method.
    }

    /**
     * @param array $post
     * @param array $context
     * @return string|null
     */
    public function processPost($post, $context)
    {
        if (array_key_exists(self::ELEMENT_BACK, $post)) {
            return self::RESULT_BACK;
        }

        return parent::processPost($post, $context);
    }
}
