<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 */

/**
 * Interface fuer Formulare die Model-Instanzen anzeigen.
 *
 * @category    Application
 * @package     Application_Form
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2009-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
interface Application_Form_IModel
{

    /**
     * Erzeugt Model-Instanz fuer Formular, entweder neue oder existierende.
     * @return mixed
     */
    public function getModel();

    /**
     * Liefert die gesetzte Modelklasse fuer das Formular.
     * @return mixed
     */
    public function getModelClass();

    /**
     * Verarbeitet POST Daten.
     * @param $post POST Daten fuer Formular
     * @param $context POST Daten fuer gesamten Request
     * @return mixed
     */
    public function processPost($post, $context);

    /**
     * Initialisiert das Formular mit Werten einer Model-Instanz.
     * @param $model
     */
    public function populateFromModel($model);

    /**
     * Aktualsiert Model-Instanz mit Werten im Formular.
     * @param $model
     */
    public function updateModel($model);

    /**
     * Bereitet das Formular fuer die Anzeige als View vor.
     */
    public function prepareRenderingAsView();
}
