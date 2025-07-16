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
 * @copyright   Copyright (c) 2023, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Document;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Shows differences between two or more documents.
 *
 * TODO more testing
 * TODO return diff report that can be rendered in different ways (console, HTML)
 * TODO add detailed diff for text values and multiline texts
 * TODO add detailed comparison of persons (recognize same persons in different order, changes in person metadata)
 * TODO ignore order of collections, licences (order shouldn't matter)
 */
class Application_Document_DocumentDiff
{
    /** @var OutputInterface */
    private $output;

    /**
     * @param int[] $docIds
     */
    public function diff($docIds)
    {
        $metadata = [];
        $keys     = [];

        foreach ($docIds as $docId) {
            $doc              = Document::get($docId);
            $docData          = $doc->toArray();
            $keys             = array_unique(array_merge($keys, array_keys($docData)));
            $metadata[$docId] = $docData;
        }

        $maxDocId  = max(array_keys($metadata));
        $maxDigits = strlen((string) $maxDocId);

        sort($keys);

        $differences = [];

        foreach ($keys as $key) {
            $values = [];

            foreach ($metadata as $docId => $docData) {
                if (isset($docData[$key])) {
                    $value = $docData[$key];
                } else {
                    $value = null;
                }

                $values[$docId] = $value;
            }

            $previousValue = null;
            $firstValue    = true;

            foreach ($values as $value) {
                if ($firstValue) {
                    $previousValue = $value;
                    $firstValue    = false;
                } else {
                    if ($previousValue !== $value) {
                        $differences[$key] = $values;
                    }
                }
            }
        }

        $diffKeys = array_keys($differences);

        $output = $this->getOutput();

        foreach ($diffKeys as $key) {
            $output->writeln("Field: <fg=yellow;options=bold>$key</>");
            $values          = $differences[$key];
            $field           = $doc->getField($key);
            $valueModelClass = $field->getValueModelClass();
            $linkModelClass  = $field->getLinkModelClass();

            foreach ($values as $docId => $value) {
                $output->writeln('------------------------------------------------------------');
                if (! is_array($value)) {
                    $line = sprintf(" <fg=green>%{$maxDigits}d</>: %s", $docId, $value);
                    $output->writeln($line);
                } else {
                    if (count($value) > 0) {
                        if ($valueModelClass !== null) {
                            if ($field->hasMultipleValues()) {
                                foreach ($value as $subValue) {
                                    $this->renderModel($key, $valueModelClass, $linkModelClass, $subValue, $docId, $maxDigits);
                                }
                            } else {
                                $this->renderModel($key, $valueModelClass, $linkModelClass, $value, $docId, $maxDigits);
                            }
                        }
                    } else {
                        $line = sprintf(" <fg=green>%{$maxDigits}d</>: %s", $docId, '-');
                        $output->writeln($line);
                    }
                }
            }
            $output->writeln('------------------------------------------------------------');
            $output->writeln('');
        }
    }

    /**
     * @param string      $key
     * @param string|null $valueModelClass
     * @param string|null $linkModelClass
     * @param mixed       $value
     * @param int         $docId
     * @param int         $maxDigits
     */
    protected function renderModel($key, $valueModelClass, $linkModelClass, $value, $docId, $maxDigits)
    {
        if (strpos($key, 'Person') === 0) {
            unset($value['DateOfBirth']); // TODO date field causes problems with fromArray
        }

        if ($key === 'File') {
            unset($value['ServerDateSubmitted']); // TODO date field causes problems with fromArray
        }

        if ($linkModelClass !== null) {
            $model = $linkModelClass::fromArray($value);
        } else {
            $model = $valueModelClass::fromArray($value);
        }

        switch ($key) {
            case 'Collection':
                $displayName = $model->getDisplayName();
                break;

            case 'EmbargoDate':
            case 'CompletedDate':
            case 'PublishedDate':
            case 'ServerDatePublished':
            case 'ServerDateCreated':
            case 'ServerDateModified':
            case 'ThesisDateAccepted':
                $displayName = (string) $model;
                break;

            case 'Enrichment':
                $displayName = '<fg=magenta>' . $model->getKeyName() . '</>: ' . $model->getValue();
                break;

            case 'Identifier':
                $displayName = '<fg=magenta>' . $model->getType() . '</>: ' . $model->getValue();
                break;

            case 'TitleMain':
            case 'TitleAbstract':
            case 'TitleAdditional':
            case 'TitleParent':
            case 'TitleSub':
                $displayName = '(<fg=magenta>' . $model->getLanguage() . '</>) ' . $model->getValue();
                break;

            case 'Note':
                $displayName = $model->getMessage();
                break;

            case 'Patent':
                $displayName = $model->getNumber() . ' - ' . $model->getApplication();
                break;

            case 'Subject':
                $displayName = $model->getValue() . ' (<fg=magenta>' . $model->getType() . '</>)';
                break;

            case 'Person':
                $displayName = $model->getDisplayName() . ' (<fg=magenta>' . $model->getRole() . '</>)';
                break;

            case 'File':
                $displayName = $model->getLabel() . ' (<fg=magenta>' . $model->getMimeType() . '</>)';
                break;

            default:
                $displayName = $model->getDisplayName();
        }

        $line = sprintf(" <fg=green>%{$maxDigits}d</>: %s", $docId, $displayName);
        $this->getOutput()->writeln($line);
    }

    /**
     * @param OutputInterface $output
     * @return $this
     */
    public function setOutput($output)
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        if ($this->output === null) {
            $this->output = new ConsoleOutput();
        }
        return $this->output;
    }
}
