<?php

namespace App\Tests\Import;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CommandTest extends KernelTestCase
{
    private CommandTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:import');
        $this->tester = new CommandTester($command);
    }

    public function testWithoutOptions(): void
    {
        $this->testerExecute(
            __DIR__ . '/csv/multiple_1.csv',
            [
            ],
        );

        $this->tester->assertCommandIsSuccessful();
        $this->checkOutput([
            'Processed: 1',
            'Success: 1',
        ]);
    }

    public function testWithoutHeader(): void
    {
        $this->testerExecute(
            __DIR__ . '/csv/multiple_4.csv',
            [
                '--haveHeader' => '0',
            ],
        );

        $this->tester->assertCommandIsSuccessful();
        $this->checkOutput([
            'Processed: 1',
            'Success: 1',
        ]);
    }

    public function testWithCustomDelimiterAndEnclosure(): void
    {
        $this->testerExecute(
            __DIR__ . '/csv/multiple_2.csv',
            [
                '-d' => '|',
                '-a' => '"',
            ],
        );

        $this->tester->assertCommandIsSuccessful();
        $this->checkOutput([
            'Processed: 1',
            'Success: 1',
        ]);
    }

    public function testWith2ValidAnd3InvalidRows(): void
    {
        $this->testerExecute(
            __DIR__ . '/csv/2_valid_3_invalid.csv',
            [
            ],
        );

        $this->tester->assertCommandIsSuccessful();
        $this->checkOutput([
            'Processed: 5',
            'Success: 2',
            'Failed: 3',
            'P0007, 24” Monitor, Awesome, , 35.99, ',
            'P0011, Misc Cables, error in export, , , ',
            'P0028, Bluray Player, Plays bluray\'s, 32, 1100.04, yes',
        ]);
    }

    /**
     * @param string $file
     * @param array<string, mixed> $options
     *
     * @return void
     */
    private function testerExecute(string $file, array $options) : void
    {
        $args = array_merge(
            $options,
            [
                'files' => $file,
                '--testmode' => true,
            ],
        );
        $this->tester->execute($args);
    }

    /**
     * @param string[] $expectedStrings
     *
     * @return void
     */
    private function checkOutput(array $expectedStrings): void
    {
        $output = $this->tester->getDisplay();
        foreach($expectedStrings as $string) {
            $this->assertStringContainsString($string, $output);
        }
    }
}
