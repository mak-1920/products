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

    public function test4ValidFiles(): void
    {
        $this->testerExecute(
            sprintf('%1$s/csv/multiple_1.csv,%1$s/csv/multiple_2.csv,%1$s/csv/multiple_3.csv,%1$s/csv/multiple_4.csv', __DIR__),
            [
                '-d' => ',||,',
                '-a' => ' "" ',
                '--haveHeader' => '1100',
            ]
        );

        $this->checkOutput([
            'Processed: 4',
            'Success: 4',
        ]);
    }

    public function test4InvalidFilesBySyntax(): void
    {
        $this->testerExecute(
            sprintf('%1$s/csv/nv_multiple_1.csv,%1$s/csv/nv_multiple_2.csv,%1$s/csv/nv_multiple_3.csv,%1$s/csv/nv_multiple_4.csv', __DIR__),
            [
                '-d' => ',||,',
                '-a' => ' "" ',
                '--haveHeader' => '1100',
            ]
        );

        $this->checkOutput([
            'Processed: 4',
            'Failed: 4',
            'TV, P0001, , 32” Tv, 10, ',
            'P0009, Harddisk, Great for storing data, 0, , ',
            'P0010, , Great for storing data, 0, 99.99, ',
            ', TV, 32” Tv, 10, 399.99, ',
        ]);
    }

    public function test3InvalidFilesByRules(): void
    {
        $this->testerExecute(
            sprintf('%1$s/csv/nv_mult_r_1.csv,%1$s/csv/nv_mult_r_2.csv', __DIR__),
            [
            ]
        );

        $this->checkOutput([
            'Processed: 2',
            'Failed: 2',
            'P0017, CPU, Processing power, 9, 4.99, ',
            'P0027, VCR, Plays videos, 34, 1000.01, yes',
        ]);
    }

    public function testOptionsWithLessCharactersForValidFiles(): void
    {
        $this->testerExecute(
            sprintf('%1$s/csv/multiple_2.csv,%1$s/csv/multiple_3.csv,%1$s/csv/multiple_4.csv,%1$s/csv/multiple_1.csv', __DIR__),
            [
                '-d' => '||',
                '-a' => '""',
                '--haveHeader' => '100',
            ]
        );

        $this->checkOutput([
            'Processed: 4',
            'Success: 4',
        ]);
    }

    public function testBigFile():void
    {
        $this->testerExecute(
            __DIR__.'/csv/bf/1_2451_2549.csv',
            [
            ]
        );

        $this->checkOutput([
            'Processed: 5000',
            'Success: 2451',
            'Failed: 2549',
        ]);

        $this->checkFailedInBigFiles('1_f_2549.csv');
    }

    public function test3BigFiles():void
    {
        $this->testerExecute(
            sprintf('%1$s/csv/bf/1_2451_2549.csv,%1$s/csv/bf/2_2472_2528.csv,%1$s/csv/bf/3_2520_2480.csv', __DIR__),

            [
            ]
        );

        $this->checkOutput([
            'Processed: 15000',
            'Success: ' . 2451 + 2472 + 2520,
            'Failed: ' . 2549 + 2528 + 2480,
        ]);

        $this->checkFailedInBigFiles('1_f_2549.csv');
        $this->checkFailedInBigFiles('2_f_2528.csv');
        $this->checkFailedInBigFiles('3_f_2480.csv');
    }

    private function checkFailedInBigFiles(string $fileName): void
    {
        $output = $this->tester->getDisplay();
        $failed = explode('\n', file_get_contents(__DIR__.'/csv/bf/failed/'.$fileName));

        foreach($failed as $row) {
            $row = str_replace(',', ', ', $row);
            $this->assertStringContainsString($row, $output);
        }
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
