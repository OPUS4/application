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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Zend_Form Wrapper für Unterformulare (Zend_Form_SubForm).
 *
 * Für das Metadaten-Formular muss jedes Unterformlar und auch das Root-Formular (Admin_Form_Document) die Klasse
 * Admin_Form_AbstractDocumentSubForm erweitern. Das das Hauptformular ein Zend_Form sein muss wird dieser Wrapper
 * eingesetzt. Theoretisch ist ein Zend_Form_SubForm auch ein Zend_Form, aber es ist nicht klar, was genau getan
 * werden müsste, um es als solches wieder einzusetzen und ohne Wrapper auszukommen.
 *
 * Durch den Wrapper werden die IDs im Formular um einen Schritt länger und fangen mit 'Document-' an. Bei der
 * Verarbeitung des POST wird die erste Ebene übersprungen ($post['Document']) und nur mit dem obersten Unterformular
 * weitergearbeitet.
 */
class Admin_Form_Wrapper extends Zend_Form
{
    /** @var string */
    private $subFormName;

    /**
     * @param parent     $subform
     * @param string     $name
     * @param array|null $options
     */
    public function __construct($subform, $name = 'Document', $options = null)
    {
        parent::__construct($options);
        $this->subFormName = $name;
        $this->addSubForm($subform, $name);
    }

    public function loadDefaultDecorators()
    {
        parent::loadDefaultDecorators();

        $this->setDecorators(
            [
                'FormElements',
                'Form',
                ['HtmlTag', ['tag' => 'div', 'class' => 'metadata-form']],
            ]
        );
    }

    /**
     * @return parent|null
     */
    public function getWrappedForm()
    {
        return $this->getSubForm($this->subFormName);
    }
}
