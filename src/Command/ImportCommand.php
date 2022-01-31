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
            ->setHelp('Files is separated by ",", characters are written without separators ')
            ->addArgument('files', InputArgument::REQUIRED, 'array of CSV file for import')
            ->addOption('delimiter', 'd', InputOption::VALUE_REQUIRED, 'Columns separator character for each file')
            ->addOption('enclosure', 'a', InputOption::VALUE_REQUIRED, 'Character around fields for each file')
            ->addOption('escape', 's', InputOption::VALUE_REQUIRED, 'Rows separator character for each file')
            ->addOption('haveHeader', null, InputOption::VALUE_REQUIRED, '1 if CSV have header, 0 if haven\'t for each file')
            ->addOption('testmode', 't', InputOption::VALUE_NONE, 'Enable testmode and don\'t save data in DB')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $files = explode(',', $input->getArgument('files'));

        $settings = $this->getCSVSettings($input, count($files));
        $import = $this->getImport($files, $settings, $input);

        $this->printImportStatus($import, $io);

        return Command::SUCCESS;
    }

    /**
     * @param InputInterface $input
     * @param int $fileCount
     *
     * @return CSVSettings[]
     */
    private function getCSVSettings(InputInterface $input, int $fileCount): array
    {
        $settings = [];

        $options = ['delimiter' => [], 'enclosure' => [], 'escape' => [], 'haveHeader' => []];

        $this->setCharacters($options, $input, $fileCount);

        for ($i = 0; $i < $fileCount; ++$i) {
            $setting = new CSVSettings();

            foreach ($options as $option => $val) {
                if ($this->isValidCharacter($val[$i])) {
                    call_user_func([$setting, 'set'.$option], $val[$i]);
                }
            }

            $settings[] = $setting;
        }

        return $settings;
    }

    /**
     * @param array<string, string[]> &$characters
     * @param InputInterface $input
     * @param int $fileCount
     *
     * @return void
     */
    private function setCharacters(array &$characters, InputInterface $input, int $fileCount): void
    {
        foreach ($characters as $option => &$set) {
            $val = $input->getOption($option) ?? '';
            $set = array_pad(
                str_split($val),
                $fileCount,
                constant(CSVSettings::class.'::DEF_CHAR_'.mb_strtoupper($option))
            );
        }
    }

    /**
     * @param string[] $files
     * @param CSVSettings[] $settings
     * @param InputInterface $input
     *
     * @return ImportCSV
     */
    private function getImport(array $files, array $settings, InputInterface $input): ImportCSV
    {
        $import = new ImportCSV(
            $this->getFiles($files),
            $settings,
            $this->isTest($input),
            $this->saver,
        );

        $import->saveRequests();

        return $import;
    }

    /**
     * @param string[] $files
     *
     * @return UploadedFile[]
     */
    private function getFiles(array $files): array
    {
        $uploadedFiles = [];

        foreach ($files as $file) {
            $uploadedFiles[] = new UploadedFile($file, $file);
        }

        return $uploadedFiles;
    }

    /**
     * @param ImportCSV $import
     * @param SymfonyStyle $io
     *
     * @return void
     */
    private function printImportStatus(ImportCSV $import, SymfonyStyle $io): void
    {
        $this->printInvalidFiles($import, $io);
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
     * @return void
     */
    private function printInvalidFiles(ImportCSV $import, SymfonyStyle $io): void
    {
        $files = $import->getNotParsedFiles();

        if(count($files) > 0) {
            $io->writeln('<fg=white;bg=red>Unable to parse file: ' . count($files) . '</>');
            foreach ($files as $file) {
                $io->writeln('<fg=white;bg=red>' . $file . '</>');
            }
            $io->writeln('<fg=white;bg=red>Check settings</>');
        }
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
