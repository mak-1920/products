<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Services\Import\ImportRequest;
use App\Services\Import\Savers\Saver;

abstract class Import
{
    /** @var ImportRequest[] $requests **/
    protected array $requests;

    /** @var ImportRequest[] $failed */
    private array $failed;

    /** @var ImportRequest[] $complete */
    private array $complete;

    public function __construct(
        array $data,
        protected bool $isTest,
        private ?Saver $saver = null,
    )
    {
        $this->failed = [];
        $this->complete = [];
        $this->setRequestsFromData($data);
        $this->sortRequestsByGroups();
    }

    abstract protected function setRequestsFromData(array $data) : void;

    public function getFailed() : array
    {
        return $this->failed;
    }

    public function getComplete() : array
    {
        return $this->complete;
    }

    public function SaveRequests() : void
    {
        if($this->isTest) {
            return;
        }

        $this->saver->Save($this->complete);
    }

    private function sortRequestsByGroups() : void 
    {
        foreach($this->requests as $request) {
            if($this->checkValidation($request)) {
                $this->complete[] = $request;
            } else {
                $this->failed[] = $request;
            }
        }
    }

    private function checkValidation(ImportRequest $request) : bool
    {
        return $request->getIsValid()
            && !($request->getCost() < 5 && $request->getCount() < 10)
            && !($request->getCost() > 1000);
    }
}