<?php

declare(strict_types=1);

namespace App\Command;

use App\Services\Import\CSV\CSVSettings;
use App\Services\Import\Logger\Logger;
use App\Services\Import\Savers\DoctrineSaver;
use App\Services\Import\TempFilesManager;
use App\Services\RabbitMQ\Import\SendProducer;
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
        private SendProducer $producer,
        private Logger $logger,
        private TempFilesManager $filesManager,
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
//            ->addOption('testmode', 't', InputOption::VALUE_NONE, 'Enable testmode and don\'t save data in DB')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $files = explode(',', $input->getArgument('files'));

        $settings = $this->getCSVSettings($input, count($files));
        $files = $this->getFiles($files);
        $savedFiles = $this->filesManager->saveFiles($files);

        $ids = $this->logger->createStatuses([
            'files' => $savedFiles,
            'settings' => $settings,
        ]);
        $this->producer->sendIDs($ids);

        $io->text('Files have been uploaded and will be processed!');

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
     * @param string|null $character
     *
     * @return bool
     */
    private function isValidCharacter(?string $character): bool
    {
        return !is_null($character) && 1 === strlen($character);
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
}
