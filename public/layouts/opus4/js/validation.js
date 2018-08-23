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
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */


function validateISSN(value) {
    // var messages = {
    //     invalidCheckdigit: "The check digit of \'%value%\' is not valid",
    //     invalidFormat: "\'%value%\' is malformed"
    // };

    // check length
    if (value.length !== 9) {
        //alert(messages.invalidFormat.replace("%value%", value));
        return false;
    }

    // check form
    if (value.match(/^[0-9]{4}[-][0-9]{3}[0-9X]$/g) === null) {
        //alert(messages.invalidFormat.replace("%value%", value));
        return false;
    }

    // Split ISSN into its parts
    var issn = value.split("");

    // Calculate and compare check digit
    var checkdigit = calculateCheckDigitISSN(issn);
    if (checkdigit != issn[8]) {
        //alert(messages.invalidCheckdigit.replace("%value%", value));
        return false;
    }

    return true;
}

function calculateCheckDigitISSN(value) {
    var z = value;
    var checkdigit = 0;
    var check = (8 * z[0] + 7 * z[1] + 6 * z[2] + 5 * z[3] + 4 * z[5] + 3 * z[6] + 2 * z[7]);
    if (11 - (check % 11) === 10) {
        checkdigit = "X";
    } else {
        checkdigit = 11 - (check % 11);
    }

    return checkdigit;
}