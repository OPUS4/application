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
    var messages = {
        invalidCheckdigit: "The check digit of \'%value%\' is not valid",
        invalidFormat: "\'%value%\' is malformed"
    };

    // check length
    if (value.length !== 9) {
        return messages.invalidFormat.replace("%value%", value);
    }

    // check form
    if (value.match(/^[0-9]{4}[-][0-9]{3}[0-9X]$/g) === null) {
        return messages.invalidFormat.replace("%value%", value);
    }

    // Split ISSN into its parts
    var issn = value.split("");

    // Calculate and compare check digit
    var checkdigit = calculateCheckDigitISSN(issn);
    if (checkdigit != issn[8]) {
        return messages.invalidCheckdigit.replace("%value%", value);
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

function validateISBN(value) {
    var messages = {
        invalidCheckdigit: "The check digit of \'%value%\' is not valid",
        invalidFormat: "\'%value%\' is malformed"
    };

    var isbnDigits = splitISBN(value);

    if (isbnDigits.length === 10) {
        return validateISBN10(value);
    }
    else if (isbnDigits.length === 13) {
        return validateISBN13(value);
    }
    else {
        return messages.invalidFormat.replace("%value%", value);
    }
}

function validateISBN10(value) {
    var messages = {
        invalidCheckdigit: "The check digit of \'%value%\' is not valid",
        invalidFormat: "\'%value%\' is malformed"
    };

    if (value.length !== 10 && value.length !== 13) {
        return messages.invalidFormat.replace("%value%", value);
    }

    if (value.match(/^[\d]*((-|\s)?[\d]*){2}((-|\s)?[\dX])$/g) === null) {
        return messages.invalidFormat.replace("%value%", value);
    }

    if (value.match(/-/) !== null && value.match(/\s/) !== null) {
        return messages.invalidFormat.replace("%value%", value);
    }

    var isbnDigits = splitISBN(value);
    return calculateCheckDigitISBN10(isbnDigits);
}

function validateISBN13(value) {
    var messages = {
        invalidCheckdigit: "The check digit of \'%value%\' is not valid",
        invalidFormat: "\'%value%\' is malformed"
    };

    if (value.length !== 13 && value.length !== 17) {
        return messages.invalidFormat.replace("%value%", value);
    }

    if (value.match(/^(978|979)((-|\s)?[\d]*){4}$/g) === null) {
        return messages.invalidFormat.replace("%value%", value);
    }

    if (value.match(/-/) !== null && value.match(/\s/) !== null) {
        return messages.invalidFormat.replace("%value%", value);
    }

    var isbnDigits = splitISBN(value);
    return calculateCheckDigitISBN13(isbnDigits);
}

function calculateCheckDigitISBN10(value) {
    var z = value;

    if (z[9] === "X") {
        z[9] = 10;
    }
    z = z.map(Number);
    var check = 10 * z[0] + 9 * z[1] + 8 * z[2] + 7 * z[3] + 6 * z[4] + 5 * z[5] + 4 * z[6] + 3 * z[7] + 2 * z[8] + 1 * z[9];

    return (check % 11 === 0);
}

function calculateCheckDigitISBN13(value) {
    var z = value.map(Number);

    var check = (z[0] + z[2] + z[4] + z[6] + z[8] + z[10] + z[12]) + 3 * (z[1] + z[3] + z[5] + z[7] + z[9] + z[11]);
    return (check % 10 === 0);
}


function splitISBN(value) {
    var isbn = value.split(/(-|\s)/);
    var digits = [];
    isbn.forEach(function (isbn) {
        if (isbn.match((/(-|\s)/))) {
            return true;
        }
        var isbn_parts = isbn.split("");
        isbn_parts.forEach(function (isbn_parts) {
            digits.push(isbn_parts);
        });
    });

    return digits;
}


$(document).ready(function () {
    var selectors = [];
    var result;
    var ident;

    var identifier = $("#fieldset-Identifiers tbody tr td.Value-data");
    var identifierText = $("#fieldset-Identifiers tbody tr :text");
    var identifierSelector = $("#fieldset-Identifiers tbody tr select");

    $.each(identifier, function (index, value) {
        var para = document.createElement("p");
        para.classList.add("datahint");
        para.setAttribute("style", "display : none");
        value.appendChild(para);
    });

    $.each(identifierSelector, function (index, value) {
        selectors[index] = value.value;
        value.onchange = function () {
            selectors[index] = value.value;
            ident = selectors[index];
        };
    });


    $.each(identifierText, function (index, value) {

        value.onchange = function () {
            if (ident === "isbn") {
                result = validateISBN(value.value);
            }
            else if (ident === "issn") {

                result = validateISSN(value.value);
            }
            else {
                result = true;
            }
            if (result !== true) {
                $(identifier[index]).find("p")[0].innerHTML = result;
                $(identifier[index]).find("p").removeAttr("style");
            }
            else {
                $(identifier[index]).find("p").attr("style", "display : none");
            }
        };
    });

});