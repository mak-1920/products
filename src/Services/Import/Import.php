<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Services\Import\Savers\Saver;

abstract class Import
{
    /** @var ImportRequest[] $requests * */
    protected array $requests;

    /**
     * @param string[] $data
     * @param bool $isTest
     * @param Saver|null $saver
     */
    public function __construct(
        array $data,
        protected bool $isTest,
        private ?Saver $saver = null,
    ) {
        $this->requests = [];
        $this->setRequestsFromData($data);
    }

    /**
     * @param string[] $data
     *
     * @return void
     */
    abstract protected function setRequestsFromData(array $data): void;

    /**
     * @return ImportRequest[]
     */
    public function getRequests(): array
    {
        return $this->requests;
    }

    /**
     * @return ImportRequest[]
     */
    public function getFailed(): array
    {
        return $this->getCompleteOrFailed(false);
    }

    /**
     * @return ImportRequest[]
     */
    public function getComplete(): array
    {
        return $this->getCompleteOrFailed(true);
    }

    /**
     * @param bool $isComplete
     *
     * @return ImportRequest[]
     */
    private function getCompleteOrFailed(bool $isComplete): array
    {
        $result = [];

        foreach ($this->requests as $request) {
            if ($request->getIsValid() === $isComplete) {
                $result[] = $request;
            }
        }

        return $result;
    }

    /**
     * @return void
     */
    public function saveRequests(): void
    {
        if ($this->isTest) {
            return;
        }

        $this->saver->Save($this->requests);
    }
}
