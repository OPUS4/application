<?php
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
 * @category    Application Unit Test
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class BootstrapTest extends ControllerTestCase
{

    public function testBootstrap()
    {
        $this->markTestSkipped();

        $count = 0;
        $iterations = 50;
        $runtime = microtime(true);
        while (true) {
            $count++;
            $this->setUp();
            if ($count % $iterations === 0) {
                $delta = (microtime(true) - $runtime) / $iterations;
                $mem_now = round(memory_get_usage() / 1024 / 1024);
                $mem_peak = round(memory_get_peak_usage() / 1024 / 1024);
                echo date('Y-m-d H:i:s') . " memory $mem_now MB, peak memory $mem_peak MB\n";
                echo "runtime per iteration (after $count iterations) (sec): $delta\n";
                $runtime = microtime(true);
            }
        }
    }
}
