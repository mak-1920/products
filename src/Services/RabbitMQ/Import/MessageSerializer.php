<?php

namespace App\Services\RabbitMQ\Import;

use App\Services\RabbitMQ\MessageSerializerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MessageSerializer implements MessageSerializerInterface
{
    /**
     * @param array $data
     *
     * @return string
     */
    public function serialize(array $data): string
    {
        $data['files'] = $this->getNamesAndPathsOfFiles($data['files']);

        return serialize($data);
    }

    /**
     * @param UploadedFile[] $files
     *
     * @return string[][]
     */
    private function getNamesAndPathsOfFiles(array $files): array
    {
        $res = [];

        foreach ($files as $file) {
            $res[] = [
                'path' => $file->getRealPath(),
                'name' => $file->getClientOriginalName(),
            ];
        }

        return $res;
    }

    /**
     * @param AMQPMessage $msg
     *
     * @return array
     */
    public function deserialize(AMQPMessage $msg): array
    {
        $data = unserialize($msg->body);

        $data['files'] = $this->setFilesByNamesAndPaths($data['files']);

        return $data;
    }

    /**
     * @param string[][] $files
     *
     * @return UploadedFile[]
     */
    private function setFilesByNamesAndPaths(array $files): array
    {
        $res = [];

        foreach ($files as $file) {
            $res[] = new UploadedFile($file['path'], $file['name']);
        }

        return $res;
    }
}
