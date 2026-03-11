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
 * @copyright   Copyright (c) 2026, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Application_Export_ExportService;
use Opus\Common\Document;
use Opus\Common\Repository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Command for creating exports.
 *
 * TODO currently the command only supports full exports (support search)
 * TODO support old import implementation
 * TODO support BibTeX
 * TODO support generic XSLT (example)
 * TODO support MARCXML
 * TODO support CSV
 * TODO support RIS
 * TODO output memory usage and runtime
 *
 * TODO is currently Export_Model_XsltExport
 * TODO currently need request, response and view
 * TODO keep existing classes, but move actual export code into separate component
 */
class Application_Console_Export_ExportCommand extends Command
{
    public const OPTION_OUTPUT = 'output';

    public const OPTION_FORMAT = 'format';

    public const OPTION_LIST = 'list';

    protected function configure()
    {
        parent::configure();

        $help = <<<EOT
EOT;

        $this->setName('export:export')
            ->setDescription('Export all documents')
            ->setHelp($help)
            ->addOption(
                self::OPTION_OUTPUT,
                'o',
                InputOption::VALUE_REQUIRED,
                'Name of output file'
            )
            ->addOption(
                self::OPTION_FORMAT,
                'f',
                InputOption::VALUE_REQUIRED,
                'Export format'
            )
            ->addOption(
                self::OPTION_LIST,
                'l',
                InputOption::VALUE_NONE,
                'List available export formats'
            )
            ->setAliases(['export']);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $exportService = $this->getExportService();

        if ($input->getOption(self::OPTION_LIST)) {
            // TODO 'index' is basic 'xml'
            $plugins = $exportService->getAllPlugins();
            foreach ($plugins as $name => $plugin) {
                $output->writeln(sprintf('<info>%s</info>', $name));
            }
            return Command::SUCCESS;
        }

        $format       = $input->getOption(self::OPTION_FORMAT) ?? 'index';
        $exportPlugin = $exportService->getPlugin($format);
        if ($exportPlugin === null) {
            $output->writeln(sprintf('<error>Format \'%s\' not supported</error>', $format));
            return Command::FAILURE;
        }

        // TODO initially only FULL exports are supported
        $result = $this->search();

        if ($result === null || $result->getDocumentCount() === 0) {
            $output->writeln('No documents found');
            return Command::FAILURE;
        }

        $outputFile = $input->getOption(self::OPTION_OUTPUT);
        if ($outputFile !== null) {
            // TODO check if file exists and verify overwriting
            $exportOutput = new StreamOutput(fopen($outputFile, 'w'));
        } else {
            $exportOutput = $output;
        }

        $stopwatch = new Stopwatch();
        $stopwatch->start('export');

        $exportPlugin->init();

        // TODO use export plugin for format (old plugings are designe for web - refactoring)
        $exportPlugin = new Application_Export_XmlExport();
        $exportPlugin->setOutput($exportOutput);
        $exportPlugin->loadStylesheet('bibtex');

        $exportPlugin->execute($result); // result = 0 or error code

        $event = $stopwatch->stop('export');

        // TODO only output if export goes to file
        $output->writeln(sprintf(
            "Export finished (%s, %s, %d Documents)",
            Helper::formatMemory($event->getMemory()),
            Helper::formatTime($event->getDuration() / 1000, 3),
            $result->getDocumentCount(),
        ));

        return Command::SUCCESS;
    }

    /**
     * TODO export Solr search and single document exports
     *
     * @return Application_Export_SearchResult
     * @throws \Opus\Common\Config\ConfigException
     */
    protected function search(): Application_Export_SearchResult
    {
        $finder = Repository::getInstance()->getDocumentFinder();
        $finder->setServerState(Document::STATE_PUBLISHED);
        $documentIds = $finder->getIds();
        return new Application_Export_SearchResult($documentIds);
    }

    protected function getExportService(): Application_Export_ExportService
    {
        $exportService = new Application_Export_ExportService();
        $exportService->loadPlugins();
        return $exportService;
    }
}
