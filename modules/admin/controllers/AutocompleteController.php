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
 *
 * @category    Application
 * @package     Module_Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Sascha Szott <opus-development@saschaszott.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Controller for providing JSON formatted data used for autocomplete
 * functions in forms.
 *
 * TODO should we better rename this controller to RestController as it
 *      is not exclusively responsible for auto completion?
 */
class Admin_AutocompleteController extends Application_Controller_ModuleAccess
{

    public function init()
    {
        parent::init();

        $this->disableViewRendering();

        $this->getResponse()->setHeader('Content-Type', 'application/json');
    }


    public function subjectAction()
    {
        $term = $this->getRequest()->getParam('term');

        if (! is_null($term)) {
            $provider = new Application_Data_SubjectProvider();

            $data = $provider->getValues($term);
        }

        echo json_encode($data);
    }

    public function enrichmenttypedescriptionAction()
    {
        $description = '';

        $typeName = $this->getRequest()->getParam('typeName');
        if (! is_null($typeName) && $typeName !== '') {
            $typeName = 'Opus_Enrichment_' . $typeName;
            $allTypes = Opus_Enrichment_AbstractType::getAllEnrichmentTypes(true);
            if (in_array($typeName, $allTypes)) {
                $typeObj = new $typeName();
                $typeDescription = $typeObj->getDescription();
                if (! is_null($typeDescription) && $typeDescription !== '') {
                    $description = $this->view->translate($typeDescription);
                }
            }
        }

        echo json_encode(['typeName' => $description]);
    }
}
