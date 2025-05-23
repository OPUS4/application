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
 * @copyright   Copyright (c) 2024, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\EnrichmentKey;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Command for listing enrichments.
 *
 * TODO option to only list used enrichments
 * TODO show number of documents using enrichment key
 * TODO move export code to other class for handling yaml enrichment configurations (export & import)
 */
class Application_Console_Model_EnrichmentExportCommand extends Command
{
    public const ARGUMENT_KEYS = 'keys';

    public const OPTION_OUTPUT_FILE = 'outputFile';

    protected function configure()
    {
        parent::configure();

        $help = <<<EOT
The <fg=green>enrichment:export</> command allows exporting the configuration of
one or all enrichment keys. 
EOT;

        $this->setName('enrichment:export')
            ->setDescription('Export enrichment configurations')
            ->setHelp($help)
            ->addArgument(
                self::ARGUMENT_KEYS,
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Enrichment key(s)'
            )->addOption(
                self::OPTION_OUTPUT_FILE,
                'o',
                InputOption::VALUE_REQUIRED,
                'Name of output file'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $keys = $input->getArgument(self::ARGUMENT_KEYS);

        if (count($keys) === 0) {
            $keys = EnrichmentKey::getAll();
        } else {
            foreach ($keys as $key) {
                $enrichment = EnrichmentKey::fetchByName($key);

                if ($enrichment === null) {
                    $output->writeln("<error>Enrichment key \"{$key}\" not found</error>");
                    return 1;
                }
            }
        }

        if (count($keys) === 0) {
            $output->writeln("<info>No enrichment keys found</info>");
            return 0;
        }

        $helper = new Admin_Model_EnrichmentKeys();

        if (count($keys) === 1) {
            $data = $helper->getEnrichmentConfig($keys[0]);
        } else {
            $enrichments = [];
            foreach ($keys as $key) {
                $enrichments[] = $helper->getEnrichmentConfig($key);
            }
            $data['enrichments'] = $enrichments;
        }

        // Export lowercase keys
        $data = array_change_key_case($data, CASE_LOWER);
        $yaml = Yaml::dump($data, 6, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);

        // WORKAROUND: put first line of collection item behind dash
        $yaml = preg_replace('/\-\n\s+/', '- ', $yaml);

        $outputFile = $input->getOption(self::OPTION_OUTPUT_FILE);

        if ($outputFile === null) {
            $output->writeln($yaml);
        } else {
            file_put_contents($outputFile, $yaml);
        }

        return 0;
    }
}
