<?php

namespace App\Tests\Import;

use App\Entity\ImportStatus;
use App\Repository\ImportStatusRepository;
use App\Services\Import\Logger\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Mailer\MailerInterface;

class LoggerTest extends TestCase
{
    private function getLogger(
        ImportStatusRepository $repository,
        MailerInterface $mailer,
        LoggerInterface $logger,
    ): Logger
    {
        return new Logger($repository, $mailer, $logger);
    }

    public function testSetNewStatus(): void
    {
        $logger = $this->getLogger(
            $this->getRepository(),
            $this->getMailer(),
            $this->getTextLogger(),
        );

        $fileName = 'some.file';
        $settings = '|" 1';
        $token = '12345token12.13';
        $fileInfo = [
            'file' => $this->getFile($fileName),
            'originalName' => $fileName,
            'isRemoving' => false,
        ];

        $data = [
            $fileInfo,
            $settings,
            $token,
        ];

        /** @var ImportStatus $status */
        $status = $this->invokeMethod($logger, 'setNewStatus', $data);

        $this->assertEquals($fileName, $status->getFileOriginalName());
        $this->assertEquals($fileName.'tmp', $status->getFileTmpName());
        $this->assertEquals($settings, $status->getCsvSettings());
        $this->assertEquals($token, $status->getToken());
        $this->assertEquals('STATUS_NEW', $status->getStatus());
        $this->assertCount(0, $status->getValidRows());
        $this->assertCount(0, $status->getInvalidRows());
        $this->assertFalse($status->getRemovingFile());
    }

    public function testCreateStatus(): void
    {
        $repository = $this->getRepository();

        $repository->expects($this->once())
            ->method('addStatus')
            ->will(
                $this->returnCallback(fn ($status) => (int)$status->getToken())
            );

        $logger = $this->getLogger(
            $repository,
            $this->getMailer(),
            $this->getTextLogger(),
        );

        $fileName = 'some.file';
        $settings = '|" 1';
        $token = '13';
        $fileInfo = [
            'file' => $this->getFile($fileName),
            'originalName' => $fileName,
            'isRemoving' => false,
        ];

        $id = $logger->createStatus($fileInfo, $settings, $token);

        $this->assertEquals($token, $id);
    }

    public function testCreateStatuses(): void
    {
        $mult = 13;
        $repository = $this->getRepository();

        $repository->expects($this->any())
            ->method('addStatus')
            ->will(
                $this->returnCallback(fn ($status) => (int)$status->getFileOriginalName())
            );

        $logger = $this->getLogger(
            $repository,
            $this->getMailer(),
            $this->getTextLogger(),
        );

        $filesInfo = [];
        for($i = 1; $i <= 5; $i++) {
            $num = $i * $mult;
            $filesInfo[] = [
                'file' => $this->getFile((string)$num),
                'originalName' => (string)$num,
                'isRemoving' => false,
            ];
        }

        $ids = $logger->createStatuses($filesInfo, [], '123');

        for($i = 1; $i <= 5; $i++) {
            $this->assertEquals($i * $mult, $ids[$i-1]);
        }
    }

    /**
     * @param object $object
     * @param string $methodName
     * @param array $parameters
     *
     * @return mixed
     */
    private function invokeMethod(
        object &$object,
        string $methodName,
        array $parameters = []
    ): mixed
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * @return ImportStatusRepository
     */
    private function getRepository(): ImportStatusRepository
    {
        $repository = $this->getMockBuilder(ImportStatusRepository::class)
            ->onlyMethods(['addStatus', 'changeStatus', 'find'])
            ->disableOriginalConstructor()
            ->getMock();

        return $repository;
    }

    /**
     * @return MailerInterface
     */
    private function getMailer(): MailerInterface
    {
        $mailer = $this->getMockBuilder(MailerInterface::class)
            ->onlyMethods(['send'])
            ->disableOriginalConstructor()
            ->getMock();

        return $mailer;
    }

    /**
     * @return LoggerInterface
     */
    private function getTextLogger(): LoggerInterface
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $logger;
    }

    private function getFile(string $fileName): File
    {
        $file = $this->getMockBuilder(File::class)
            ->onlyMethods(['getRealPath'])
            ->disableOriginalConstructor()
            ->getMock();

        $file->expects($this->any())
            ->method('getRealPath')
            ->will($this->returnValue($fileName.'tmp'));

        return $file;
    }
}
