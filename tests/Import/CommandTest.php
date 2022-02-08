<?php

namespace App\Tests\Import;

use App\Command\ImportCommand;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Console\Input\InputInterface;

class CommandTest extends TestCase
{
    public function testGetFiles(): void
    {
        $res = $this->invokeMethod('getFiles', ['12,3,4']);

        $this->assertCount(3, $res);
        $this->assertEquals('12', $res[0]);
        $this->assertEquals('3', $res[1]);
        $this->assertEquals('4', $res[2]);
    }

    public function testIsValidCharacter(): void
    {
        $characters = [
            null => false,
            '' => false,
            ' ' => true,
            '1' => true,
            '12' => false,
        ];

        foreach($characters as $character => $result) {
            $this->assertEquals($result, $this->invokeMethod('isValidCharacter', [$character]));
        }
    }

    public function testSetCharacters(): void
    {
        $options = ['delimiter' => [], 'enclosure' => [], 'escape' => [], 'haveHeader' => []];

        $this->invokeMethod('setCharacters', [&$options, $this->getInput(), 1]);

        $this->assertEquals('|', $options['delimiter'][0]);
        $this->assertEquals('"', $options['enclosure'][0]);
        $this->assertEquals(' ', $options['escape'][0]);
        $this->assertEquals('0', $options['haveHeader'][0]);
    }

    public function testSetCharactersWithInvalidData(): void
    {
        $options = ['delimiter' => ['11'], 'enclosure' => [null], 'escape' => [''], 'haveHeader' => []];

        $this->invokeMethod('setCharacters', [&$options, $this->getInput(), 1]);

        $this->assertEquals('|', $options['delimiter'][0]);
        $this->assertEquals('"', $options['enclosure'][0]);
        $this->assertEquals(' ', $options['escape'][0]);
        $this->assertEquals('0', $options['haveHeader'][0]);
    }

    public function testGetCSVSetting(): void
    {

        $options = ['delimiter' => [','], 'enclosure' => [' '], 'escape' => [null], 'haveHeader' => ['0']];

        $res = $this->invokeMethod('getCSVSetting', [$options, 0]);

        $this->assertEquals(',  0', $res);
    }

    public function testGetCSVSettings(): void
    {
        $res = $this->invokeMethod('getCSVSettings', [$this->getInput(), 2]);

        $this->assertEquals('|" 0', $res[0]);
        $this->assertEquals(',  1', $res[1]);
    }

    /**
     * @param string $method
     * @param array $data
     *
     * @return mixed
     */
    private function invokeMethod(string $method, array $data): mixed
    {
        $class = new ReflectionClass(ImportCommand::class);
        $method = $class->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($this->getCommand(), $data);
    }

    /**
     * @return ImportCommand
     */
    private function getCommand(): ImportCommand
    {
        $command = $this->getMockBuilder(ImportCommand::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $command;
    }

    /**
     * @return InputInterface
     */
    private function getInput(): InputInterface
    {
        $input = $this->getMockBuilder(InputInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $input->expects($this->any())
            ->method('getOption')
            ->withConsecutive(['delimiter'], ['enclosure'], ['escape'], ['haveHeader'])
            ->willReturnOnConsecutiveCalls('|', '"', ' ', '0');

        return $input;
    }
}
