<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ImportStatus;
use App\Services\Import\CSV\CSVSettings;
use App\Services\Import\Savers\DoctrineSaver;
use App\Services\Import\Sender;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import',
    description: 'Import from CSV to DB',
)]
class ImportCommand extends Command
{
    public function __construct(
        private Sender $sender,
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $files = $this->getFiles($input->getArgument('files'));
        $settings = $this->getCSVSettings($input, count($files));
        $token = uniqid(ImportStatus::COMMAND_TOKEN_PREFIX, true);

        $this->asyncProcess($files, $settings, $token, $io);

        return Command::SUCCESS;
    }

    /**
     * @param string[] $files
     * @param string[] $settings
     * @param string $token
     * @param SymfonyStyle $io
     */
    private function asyncProcess(array $files, array $settings, string $token, SymfonyStyle $io): void
    {
        $ids = $this->sender->send($files, $settings, $token);

        $io->info('Files have been uploaded and will be processed!');
        $this->printIDs($ids, $io);
    }

    /**
     * @param InputInterface $input
     * @param int $fileCount
     *
     * @return string[]
     */
    private function getCSVSettings(InputInterface $input, int $fileCount): array
    {
        $settings = [];

        $options = ['delimiter' => [], 'enclosure' => [], 'escape' => [], 'haveHeader' => []];

        $this->setCharacters($options, $input, $fileCount);

        for ($i = 0; $i < $fileCount; ++$i) {
            $setting = '';
            foreach ($options as $option => $val) {
                if ($this->isValidCharacter($val[$i])) {
                    $setting .= $val[$i];
                } else {
                    $setting .= $this->getSettingsDefaultChar($option);
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
                $this->getSettingsDefaultChar($option)
            );
        }
    }

    /**
     * @param string $option
     *
     * @return string
     */
    private function getSettingsDefaultChar(string $option): string
    {
        return constant(CSVSettings::class.'::DEF_CHAR_'.mb_strtoupper($option));
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
     * @param string $filesInStr
     *
     * @return string[]
     */
    private function getFiles(string $filesInStr): array
    {
        $files = explode(',', $filesInStr);

        return $files;
    }

    /**
     * @param int[] $ids
     * @param SymfonyStyle $io
     *
     * @return void
     */
    private function printIDs(array $ids, SymfonyStyle $io)
    {
        $io->text('ids of requests:');
        foreach ($ids as $id) {
            $io->text('id'.$id);
        }
    }
}
