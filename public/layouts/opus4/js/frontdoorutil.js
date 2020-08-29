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
 * @author      Sascha Szott <szott@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2016, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

$(function () {
    $('.abstractFull').hide();
    $('.abstractShort').show();
    $('.abstractThreeDots').show();
    $('.abstractButtonShow').show();

    function toggleAbstract()
    {
        var id = $(this).attr('id').split("_")[1];
        $('#abstractFull_' + id).toggle();
        $('#abstractShort_' + id).toggle();
        $('#abstractThreeDots_' + id).toggle();
        $('#abstractText_' + id).toggle();
        $('#abstractButtonShow_' + id).toggle();
        $('#abstractButtonHide_' + id).toggle();
    }

    $('.abstractButton').click(toggleAbstract);
    $('.abstractThreeDots').click(toggleAbstract);

    // add key handler for navigation
    $('body').on('keydown', function (e) {
            var event = window.event ? window.event : e;

        if (event.keyCode == 37) {
            var link = $('#frontdoor-link-prev');
            if (link.length) {
                link[0].click();
            }
        } else if (event.keyCode == 39) {
            var link = $('#frontdoor-link-next');
            if (link.length) {
                link[0].click();
            }
        }
    });
});

