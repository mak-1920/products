<?php

declare(strict_types=1);

namespace App\Command;

use App\Services\Import\CSV\CSVSettings;
use App\Services\Import\CSV\ImportCSV;
use App\Services\Import\Savers\DoctrineSaver;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[AsCommand(
    name: 'app:import',
    description: 'Import from CSV to DB',
)]
class ImportCommand extends Command
{
    public function __construct(
        private DoctrineSaver $saver,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file for import')
            ->addOption('delimiter', 'd', InputOption::VALUE_REQUIRED, 'Columns separator character')
            ->addOption('enclosure', 'a', InputOption::VALUE_REQUIRED, 'Character around fields')
            ->addOption('escape', 's', InputOption::VALUE_REQUIRED, 'Rows separator character')
            ->addOption('testmode', 't', InputOption::VALUE_NONE, 'Enable testmode and don\'t save data in DB')
            ->addOption('non-header', null, InputOption::VALUE_NONE, 'Use, if CSV haven\'t header')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $file = $input->getArgument('file');

        $settings = $this->getCSVSettings($input);
        $import = $this->getImport($file, $settings, $input);

        $this->printImportStatus($import, $io);

        return Command::SUCCESS;
    }

    /**
     * @param InputInterface $input
     *
     * @return CSVSettings
     */
    private function getCSVSettings(InputInterface $input): CSVSettings
    {
        $settings = new CSVSettings();

        $options = ['delimiter', 'enclosure', 'escape'];

        foreach ($options as $option) {
            $val = $input->getOption($option);
            if ($this->isValidCharacter($val)) {
                call_user_func([$settings, 'set'.$option], $val);
            }
        }

        if (true === $input->getOption('non-header')) {
            $settings->setHaveHeader(false);
        }

        return $settings;
    }

    /**
     * @param string $file
     * @param CSVSettings $settings
     * @param InputInterface $input
     *
     * @return ImportCSV
     */
    private function getImport(string $file, CSVSettings $settings, InputInterface $input): ImportCSV
    {
        $import = new ImportCSV(
            [new UploadedFile($file, $file)],
            [$settings],
            $this->isTest($input),
            $this->saver,
        );

        $import->saveRequests();

        return $import;
    }

    /**
     * @param ImportCSV $import
     * @param SymfonyStyle $io
     *
     * @return void
     */
    private function printImportStatus(ImportCSV $import, SymfonyStyle $io): void
    {
        if (!$this->isValidFile($import, $io)) {
            return;
        }

        $this->printInfoAboutProcessedRows($import, $io);
        $this->printInfoAboutValidRows($import, $io);
        $this->printInfoAboutInvalidRows($import, $io);
    }

    /**
     * @param InputInterface $input
     *
     * @return bool
     */
    private function isTest(InputInterface $input): bool
    {
        return $input->getOption('testmode');
    }

    /**
     * @param string|null $character
     *
     * @return bool
     */
    private function isValidCharacter(?string $character): bool
    {
        return !is_null($character) && 1 === strlen($character);
    }

    /**
     * @param ImportCSV $import
     * @param SymfonyStyle $io
     *
     * @return bool
     */
    private function isValidFile(ImportCSV $import, SymfonyStyle $io): bool
    {
        if (count($import->getNotParsedFiles()) > 0) {
            $io->error('Unable to parse file. Check settings');

            return false;
        }

        return true;
    }

    /**
     * @param ImportCSV $import
     * @param SymfonyStyle $io
     *
     * @return void
     */
    private function printInfoAboutProcessedRows(ImportCSV $import, SymfonyStyle $io): void
    {
        $io->writeln('<fg=white;bg=blue>Processed: '.count($import->getRequests()).'</>');
    }

    /**
     * @param ImportCSV $import
     * @param SymfonyStyle $io
     *
     * @return void
     */
    private function printInfoAboutValidRows(ImportCSV $import, SymfonyStyle $io): void
    {
        $rows = $import->getComplete();

        if (count($rows) > 0) {
            $io->writeln('<fg=white;bg=green>Success: '.count($rows).'</>');
        }
    }

    /**
     * @param ImportCSV $import
     * @param SymfonyStyle $io
     *
     * @return void
     */
    private function printInfoAboutInvalidRows(ImportCSV $import, SymfonyStyle $io): void
    {
        $rows = $import->getFailed();

        if (count($rows) > 0) {
            $io->writeln('<fg=white;bg=red>Failed: '.count($rows).'</>');
            foreach ($rows as $row) {
                $io->writeln('<fg=white;bg=red>'.implode(', ', $row).'</>');
            }
        }
    }
}
