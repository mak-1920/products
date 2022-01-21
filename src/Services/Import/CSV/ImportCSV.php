<?php

declare(strict_types=1);

namespace App\Services\Import\CSV;

use App\Services\Import\Import;
use App\Services\Import\ImportRequest;
use App\Services\Import\Savers\Saver;
use League\Csv\Reader;
use League\Csv\SyntaxError;

class ImportCSV extends Import
{
    static private array $headerTitles = ['Product Code', 'Product Name', 'Product Description', 'Stock', 'Cost in GBP', 'Discontinued'];
    
    private bool $headerMustSynchronization;
    private Reader $csv;

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
        $this->csv = Reader::createFromPath($filePath);
        
        $this->setCSVSettings($settings);
        $this->headerMustSynchronization = $this->headerIsMustSynchronization();

        try{
            $records = $this->csv->getRecords();
        }
        catch (SyntaxError) {
            return [];
        }
        $rows = [];

        foreach($records as $record) {
            $rows[] = $record;
        }

        return $rows;
    }

    private function setCSVSettings(CSVSettings $settings) : void
    {
        $this->csv->setDelimiter($settings->getDelimiter());
        $this->csv->setEscape($settings->getEscape());
        $this->csv->setEnclosure($settings->getEnclosure());
        if($settings->isHavingHeader()) {
            $this->csv->setHeaderOffset(0);
        }
    }

    private function headerIsMustSynchronization() : bool
    {
        if($this->csv->getHeaderOffset() === null) {
            return false;
        }

        return $this->checkHeader();        
    }

    private function checkHeader() : bool
    {
        try{
            $header = $this->csv->getHeader();
        } 
        catch(SyntaxError) {
            return false;
        }

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