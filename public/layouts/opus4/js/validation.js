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

/**
 * Array with messages for the client-sided validation.
 * @type {Array}
 */
var opus4Messages = [];
opus4Messages["identifierInvalidCheckdigit"] = "The check digit of \'%value%\' is not valid";
opus4Messages["identifierInvalidFormat"] = "\'%value%\' is malformed";

// This class contains all necessary functions for ISBN-validation on client side.
var IsbnValidation = function () {};

// This function is the main-function for ISBN-validation and uses the specific validation for ISBN10 and ISBN13.
IsbnValidation.prototype.validateISBN = function (value)
{
    var isbnDigits = this.splitIsbn(value);

    if (isbnDigits.length === 10) {
        return this.validateISBN10(value);
    }
    else if (isbnDigits.length === 13) {
        return this.validateISBN13(value);
    }
    else {
        return opus4Messages["identifierInvalidFormat"].replace("%value%", value);
    }
};

IsbnValidation.prototype.validateISBN13 = function (value)
{
    if (value.length !== 13 && value.length !== 17) {
        return opus4Messages["identifierInvalidFormat"].replace("%value%", value);
    }

    if (value.match(/^(978|979)((-|\s)?[\d]*){4}$/g) === null) {
        return opus4Messages["identifierInvalidFormat"].replace("%value%", value);
    }

    if (value.match(/-/) !== null && value.match(/\s/) !== null) {
        return opus4Messages["identifierInvalidFormat"].replace("%value%", value);
    }

    var isbnDigits = this.splitIsbn(value);
    if (this.calculateCheckDigitISBN13(isbnDigits) === false) {
        return opus4Messages["identifierInvalidCheckdigit"].replace("%value%", value);
    }

    return true;
};

IsbnValidation.prototype.validateISBN10 = function (value)
{
    if (value.length !== 10 && value.length !== 13) {
        return opus4Messages["identifierInvalidFormat"].replace("%value%", value);
    }

    if (value.match(/^[\d]*((-|\s)?[\d]*){2}((-|\s)?[\dX])$/g) === null) {
        return opus4Messages["identifierInvalidFormat"].replace("%value%", value);
    }

    if (value.match(/-/) !== null && value.match(/\s/) !== null) {
        return opus4Messages["identifierInvalidFormat"].replace("%value%", value);
    }

    var isbnDigits = this.splitIsbn(value);
    if (this.calculateCheckDigitISBN10(isbnDigits) === false) {
        return opus4Messages["identifierInvalidCheckdigit"].replace("%value%", value);
    }

    return true;
};

// This function is used, to split the ISBN in its digits.
IsbnValidation.prototype.splitIsbn = function (value)
{
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
};

// The following two functions, calculates the checkdigits for ISBN10 and ISBN13.
IsbnValidation.prototype.calculateCheckDigitISBN10 = function (value)
{
    var z = value;

    if (z[9] === "X") {
        z[9] = 10;
    }
    z = z.map(Number);
    var check = 10 * z[0] + 9 * z[1] + 8 * z[2] + 7 * z[3] + 6 * z[4] + 5 * z[5] + 4 * z[6] + 3 * z[7] + 2 * z[8] + 1 * z[9];

    return (check % 11 === 0);
};

IsbnValidation.prototype.calculateCheckDigitISBN13 = function (value)
{
    var z = value.map(Number);
    
    var check = (z[0] + z[2] + z[4] + z[6] + z[8] + z[10] + z[12]) + 3 * (z[1] + z[3] + z[5] + z[7] + z[9] + z[11]);

    return (check % 10 === 0);
};

var IssnValidation = function () {};

// This class ist used for ISSN-validation on client side.
IssnValidation.prototype.validateISSN = function (value)
{
    // check length
    if (value.length !== 9) {
        return opus4Messages["identifierInvalidFormat"].replace("%value%", value);
    }

    // check form
    if (value.match(/^[0-9]{4}[-][0-9]{3}[0-9X]$/g) === null) {
        return opus4Messages["identifierInvalidFormat"].replace("%value%", value);
    }

    // Split ISSN into its parts
    var issn = value.split("");

    // Calculate and compare check digit
    var checkdigit = this.calculateCheckDigitISSN(issn);
    if (checkdigit != issn[8]) {
        return opus4Messages["identifierInvalidCheckdigit"].replace("%value%", value);
    }

    return true;
};

// This function calculates the checkdigit for a ISSN.
IssnValidation.prototype.calculateCheckDigitISSN = function (value)
{
    var z = value;
    var checkdigit = 0;
    var check = (8 * z[0] + 7 * z[1] + 6 * z[2] + 5 * z[3] + 4 * z[5] + 3 * z[6] + 2 * z[7]);
    if (11 - (check % 11) === 10) {
        checkdigit = "X";
    } else {
        checkdigit = 11 - (check % 11);
    }

    return checkdigit;
};

/**
 * This function is add to a document-formulary. It looks for all added identifier and adds the validation function to it.
 */
$(document).ready(function ()
{
    var selectors = [];
    var result;

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
        };
    });

    $.each(identifierText, function (index, value) {
        value.onchange = function () {
            if (selectors[index] === "isbn") {
                var isbnValidator = new IsbnValidation();
                result = isbnValidator.validateISBN(value.value);
            }
            else if (selectors[index] === "issn") {
                var issnValidator = new IssnValidation();
                result = issnValidator.validateISSN(value.value);
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
