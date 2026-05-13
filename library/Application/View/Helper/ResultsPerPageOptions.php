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

use Opus\Common\ConfigTrait;

/**
 * View helper for returning value of configuration option.
 */
class Application_View_Helper_ResultsPerPageOptions extends Application_View_Helper_Abstract
{
    use ConfigTrait;

    /**
     * @param int $steps
     * @return string
     */
    public function resultsPerPageOptions($steps = 0)
    {
        $options = $this->getOptions();

        $output = '<ul>';

        foreach ($options as $option => $label) {
            $searchUrl = $this->view->url(['rows' => $option]);

            $output .= "<li><a href=\"$searchUrl\">$label</a></li>";
        }

        $output .= '</ul>';

        return $output;
    }

    public function getOptions(): array
    {
        $config = $this->getConfig();

        $options = [];

        if (isset($config->search->resultsPerPageOptions)) {
            $list = $config->search->resultsPerPageOptions;
            if (strlen(trim($list)) > 0) {
                $options = array_filter(array_map('trim', explode(',', $list)));
            }
        }

        if (count($options) === 0) {
            $options = [10, 20, 50, 100]; // TODO do we need defaults in the code?
        }

        $labelled = [];

        foreach ($options as $option) {
            if (strtolower($option) === 'all') {
                $labelled['all'] = $this->view->translate('default_all');
            } else {
                $labelled[$option] = $option;
            }
        }

        return $labelled;
    }
}
