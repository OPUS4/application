<?PHP

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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\DocumentInterface;

/**
 * Unterformular fuer die Aktions im Metadaten-Formular.
 *
 * TODO Unit Tests
 */
class Admin_Form_Document_Actions extends Admin_Form_AbstractDocumentSubForm
{
    public const ELEMENT_ID = 'Id';

    public const ELEMENT_HASH = 'OpusHash';

    /**
     * Name für Button zum Speichern.
     */
    public const ELEMENT_SAVE = 'Save';

    /**
     * Name für Button zum Speichern und im Metadaten-Formular bleiben.
     */
    public const ELEMENT_SAVE_AND_CONTINUE = 'SaveAndContinue';

    /**
     * Name für Button um das Editieren abzubrechen.
     */
    public const ELEMENT_CANCEL = 'Cancel';

    public function init()
    {
        parent::init();

        $this->addElement('hidden', self::ELEMENT_ID);
        $this->addElement('hash', self::ELEMENT_HASH, ['salt' => 'unique']); // TODO salt?
        $this->addElement('submit', self::ELEMENT_SAVE, ['decorators' => ['ViewHelper']]);
        $this->addElement('submit', self::ELEMENT_SAVE_AND_CONTINUE, ['decorators' => ['ViewHelper']]);
        $this->addElement('submit', self::ELEMENT_CANCEL, ['decorators' => ['ViewHelper']]);

        $this->getElement(self::ELEMENT_SAVE)->setDisableTranslator(true);
        $this->getElement(self::ELEMENT_SAVE_AND_CONTINUE)->setDisableTranslator(true);
        $this->getElement(self::ELEMENT_CANCEL)->setDisableTranslator(true);

        $this->setDecorators([
            'PrepareElements',
            ['ViewScript', ['viewScript' => 'form/documentActions.phtml']],
            [['fieldsWrapper' => 'HtmlTag'], ['tag' => 'div', 'class' => 'fields-wrapper']],
            [
                ['divWrapper' => 'HtmlTag'],
                [
                    'tag'   => 'div',
                    'class' => 'subform',
                    'id'    => 'subform-Actions',
                ],
            ],
        ]);
    }

    /**
     * @param DocumentInterface $document
     */
    public function populateFromModel($document)
    {
        $this->getElement(self::ELEMENT_ID)->setValue($document->getId());
    }

    /**
     * @param array $post
     * @param array $context
     * @return string|null
     */
    public function processPost($post, $context)
    {
        // Prüfen, ob "Speichern" geklickt wurde
        if (array_key_exists(self::ELEMENT_SAVE, $post)) {
            return Admin_Form_Document::RESULT_SAVE;
        } elseif (array_key_exists(self::ELEMENT_SAVE_AND_CONTINUE, $post)) {
            return Admin_Form_Document::RESULT_SAVE_AND_CONTINUE;
        } elseif (array_key_exists(self::ELEMENT_CANCEL, $post)) {
            return Admin_Form_Document::RESULT_CANCEL;
        }

        return null;
    }

    /**
     * @return true
     */
    public function isEmpty()
    {
        return true;
    }
}
