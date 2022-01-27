<?php

declare(strict_types=1);

namespace App\Services\Import\Savers;

use Port\Steps\StepAggregator;

interface Saver
{
    /**
     * @param StepAggregator $transporter
     *
     * @return string[][] results
     */
    public function save(StepAggregator $transporter): array;
}
