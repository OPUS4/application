<?PHP
/*
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
 * @category    Application
 * @package     View
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2013-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Ein Text INPUT Element für OPUS Formulare.
 *
 * Zur Zeit nur vom Metadaten-Formular genutzt.
 */
class Application_Form_Element_Text extends Zend_Form_Element_Text implements Application_Form_IElement
{

    /**
     * Hinweis, der mit dem Feld angezeigt werden sollte.
     *
     * Ein Hinweis entspricht keinem Fehler und auch nicht der Feldbeschreibung. Ein Hinweis kann benutzt werden um den
     * Nutzer zu signalisieren, daß ein Wert (z.B. ISBN) nicht korrekt ist, aber trotzdem in der Datenbank gespeichert
     * werden kann. Also für zusätzliche Validierungen, die Warnungen erzeugen, aber nicht das Speichern verhindern.
     *
     * Der Hinweis wird vom Dekorator 'ElementHint' ausgegeben.
     *
     * @var string
     */
    private $_hint;

    private $_staticViewHelper = 'viewFormDefault';

    /**
     * Initialisiert das Formularelement.
     *
     * Fügt PrefixPath für angepasste OPUS Dekoratoren hinzu.
     */
    public function init()
    {
        parent::init();

        $this->addPrefixPath(
            'Application_Form_Decorator', 'Application/Form/Decorator', Zend_Form::DECORATOR
        );
    }

    /**
     * Lädt die Defaultdekoratoren für ein Textelement.
     */
    public function loadDefaultDecorators()
    {
        if (!$this->loadDefaultDecoratorsIsDisabled() && count($this->getDecorators()) == 0) {
            $this->setDecorators([
                'ViewHelper',
                'Placeholder',
                'Description',
                'ElementHint',
                'Errors',
                'ElementHtmlTag',
                ['LabelNotEmpty', ['tag' => 'div', 'tagClass' => 'label', 'placement' => 'prepend']],
                [['dataWrapper' => 'HtmlTagWithId'], ['tag' => 'div', 'class' => 'data-wrapper']]
            ]);
        }
    }

    /**
     * Setzt den Hinweis für das Formularelement.
     * @param $hint Hinweis
     */
    public function setHint($hint)
    {
        $this->_hint = $hint;
    }

    /**
     * Liefert den Hinweis für das Formularelement.
     * @return string
     */
    public function getHint()
    {
        return $this->_hint;
    }

    /**
     * Sorgt dafür, daß nur der Text ausgeben wird und kein INPUT-Tag.
     */
    public function prepareRenderingAsView()
    {
        $viewHelper = $this->getDecorator('ViewHelper');
        if ($viewHelper instanceof Application_Form_Decorator_ViewHelper) {
            $viewHelper->setViewOnlyEnabled(true);
        }
        $this->removeDecorator('Placeholder');
    }

    public function getStaticViewHelper()
    {
        return $this->_staticViewHelper;
    }

    public function setStaticViewHelper($viewHelper)
    {
        $this->_staticViewHelper = $viewHelper;
    }
}
