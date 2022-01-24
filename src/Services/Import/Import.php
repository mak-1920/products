<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Services\Import\Savers\Saver;

abstract class Import
{
    /** @var ImportRequest[] $requests * */
    protected array $requests;

    public function __construct(
        array $data,
        protected bool $isTest,
        private ?Saver $saver = null,
    ) {
        $this->requests = [];
        $this->setRequestsFromData($data);
    }

    abstract protected function setRequestsFromData(array $data): void;

    public function getRequests(): array
    {
        return $this->requests;
    }

    public function getFailed(): array
    {
        return $this->getCompleteOrFailed(false);
    }

    public function getComplete(): array
    {
        return $this->getCompleteOrFailed(true);
    }

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

    public function saveRequests(): void
    {
        if ($this->isTest) {
            return;
        }

        $this->saver->Save($this->requests);
    }
}
