<?php

namespace App\Services\RabbitMQ\Import;

use App\Services\Import\CSV\CSVSettings;
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
//        $msg = $this->getFilesAsString($data['files']);
//        $msg .= $this->getSettingsAsString($data['settings']);
//        $msg .= '#' . (int)($data['testmode'] ?? 0);

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
//        $message = $msg->body;
        $data = unserialize($msg->body);

        $data['files'] = $this->setFilesByNamesAndPaths($data['files']);
//        $data['files'] = $this->getFilesAsArray($message);

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

    /**
     * @param UploadedFile[] $files
     *
     * @return string
     */
    private function getFilesAsString(array $files): string
    {
        $str = '';

        foreach ($files as $file) {
            $str .= $file->getRealPath().',';
        }

        return $str.'|';
    }

    /**
     * @param CSVSettings[] $settings
     *
     * @return string
     */
    private function getSettingsAsString(array $settings): string
    {
        $d = '';
        $e = '';
        $h = '';
        $s = '';

        foreach ($settings as $setting) {
            $d .= str_pad($setting->getDelimiter(), 1);
            $e .= str_pad($setting->getEnclosure(), 1);
            $h .= str_pad($setting->getHaveHeader(), 1, '0');
            $s .= str_pad($setting->getEscape(), 1);
        }

        return sprintf('d%s#e%s#s%s#h%s', $d, $e, $s, $h);
    }

    /**
     * @param string $message
     *
     * @return UploadedFile[]
     */
    private function getFilesAsArray(string $message): array
    {
        $filesStr = substr($message, 0, stripos($message, '#'));
        $filesArr = explode(',', $filesStr);
        $files = [];

        foreach ($filesArr as $file) {
            $files[] = new UploadedFile($file);
        }
    }
}
