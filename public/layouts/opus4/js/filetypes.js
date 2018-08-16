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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

$(function () {
    var fileElem = $("input:file")[0];
    var maxFileSize = $("input[name=MAX_FILE_SIZE]").val();

    if (typeof fileElem !== "undefined") {
        fileElem.validFileExtensions = null; // nichts erlaubt, wird auf Publishseite überschrieben
        fileElem.errorMessages = {
            fileNameErrorMessage : "The file \'%name%\' has the following errors:",
            invalidFileTypeMessage: "The extension of file is not allowed.",
            invalidFileSizeMessage: "The size of file is not allowed. Choose a file with less then \'%size%\' byte.",
            invalidFilenameLengthMessage: "The length of your filename is too long. Your filename should have less then \'%size%\' characters.",
            invalidFilenameCharsetMessage: "Your filename has wrong characters or a wrong form.",
            anotherFileMessage: "Please choose another file."
        };


        fileElem.onchange = function () {
            var filepath = this.value.split("\\");
            var filename = filepath[filepath.length - 1];
            var fileSize = this.files[0].size;
            var pattern = new RegExp($("input[name=allowedFilenameCharset]").val());
            var maxFileNameSize = $("input[name=maxFileNameLength]").val();
            var errors = [];

            var ext = filename.match(/\.([^\.]+)$/);
            if (fileElem.validFileExtensions != null && (ext == null || $.inArray(ext[1], this.validFileExtensions) === -1)) {
                errors.push(fileElem.errorMessages["invalidFileTypeMessage"]);
            }

            if (fileSize > maxFileSize) {
                errors.push(fileElem.errorMessages["invalidFileSizeMessage"].replace("%size%", maxFileSize));
            }

            if (pattern.test(filename) === false) {
                errors.push(fileElem.errorMessages["invalidFilenameCharsetMessage"]);
            }

            if (filename.length > maxFileNameSize && maxFileNameSize > 0) {
                errors.push(fileElem.errorMessages["invalidFilenameLengthMessage"].replace("%size%", maxFileNameSize));
            }

            if (errors.length !== 0) {
                errors.unshift(fileElem.errorMessages["fileNameErrorMessage"].replace("%name%", filename));
                errors.push(fileElem.errorMessages["anotherFileMessage"]);

                alert(errors.join("\n"));
                this.value = null;
            }
        };
    }
});