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
 * @category    Application
 * @author      Julian Heise <heise@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2015, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * The following functions allow reseting the form to empty values and default values without reloading the page.
 *
 * It is not really essential. The regular reset functionality for HTML forms is not sufficient, because the form may
 * be loaded with values in the search fields. The reset buttons is supposed to empty those fields.
 *
 * @returns {boolean}
 */
function resetAdvancedSearchForm()
{
    var fields = ['author', 'title', 'referee', 'abstract', 'fulltext', 'year'];
    for (i = 0; i < fields.length; i++) {
        var id = fields[i];
        resetTextBox(id);
        resetComboBox(id + 'modifier');
    }
    resetComboBox('rows');
    return false;
}

function resetTextBox(id)
{
    var textBox = document.getElementById(id);
    if (textBox != null) {
        textBox.value = '';
    }
}

function resetComboBox(id)
{
    var comboBox = document.getElementById(id);
    if (comboBox != null) {
        comboBox.selectedIndex = 0;
    }
}
