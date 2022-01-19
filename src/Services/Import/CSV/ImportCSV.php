<?php

declare(strict_types=1);

namespace App\Services\Import\CSV;

use App\Services\Import\Import;
use App\Services\Import\ImportRequest;
use App\Services\Import\Savers\Saver;
use League\Csv\Reader;

class ImportCSV extends Import
{
    static private array $headerTitles = ['product', 'cost', 'count'];
    
    private bool $headerMustSynchronization;

    public function __construct(
        string $filePath,
        CSVSettings $csvSettings,
        bool $isTest,
        Saver $saver = null,
    )
    {
        $data = $this->setDataFromFile($filePath, $csvSettings);

        parent::__construct($data, $isTest, $saver);   
    }

    protected function setRequestsFromData(array $data) : void
    {
        foreach($data as $row) {
            if($this->headerMustSynchronization) {
                $request = [];
                for($i = 0; $i < count(self::$headerTitles); $i++){
                    $request[] = $row[self::$headerTitles[$i]];
                }
                $this->requests[] = new ImportRequest($request);
            } else {
                $row = array_values($row);
                $this->requests[] = new ImportRequest($row);
            }
        }
    }

    private function setDataFromFile(string $filePath, CSVSettings $settings) : array
    {
        $csv = Reader::createFromPath($filePath);
        
        $this->setCSVSettings($csv, $settings);
        $this->headerMustSynchronization = $this->headerIsMustSynchronization($csv);

        $records = $csv->getRecords();
        $rows = [];

        foreach($records as $record) {
            $rows[] = $record;
        }

        return $rows;
    }

    private function setCSVSettings(Reader $csv, CSVSettings $settings) : void
    {
        $csv->setDelimiter($settings->getDelimiter());
        $csv->setEscape($settings->getEscape());
        $csv->setEnclosure($settings->getEnclosure());
        if($settings->isHavingHeader()) {
            $csv->setHeaderOffset(0);
        }
    }

    private function headerIsMustSynchronization($csv) : bool
    {
        if($csv->getHeaderOffset() === null) {
            return false;
        }

        return $this->checkHeader($csv);        
    }

    private function checkHeader($csv) : bool
    {
        $header = $csv->getHeader();

        return $this->checkCountTitles($header)
            && $this->checkExistsTitles($header);
    }

    private function checkCountTitles(array $header) : bool
    {
        return count($header) == count(self::$headerTitles);
    }

    private function checkExistsTitles(array $header) : bool 
    {
        for($i = 0; $i < count(self::$headerTitles); $i++) {
            if(array_search(self::$headerTitles[$i], $header) === false) {
                return false;
            }
        }
        return true;
    }
}