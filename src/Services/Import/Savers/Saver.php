<?php

declare(strict_types=1);

namespace App\Services\Import\Savers;

use Port\Steps\StepAggregator;

interface Saver
{
    /**
     * @param StepAggregator[] $transporters
     *
     * @return string[][] results
     */
    public function save(array $transporters): array;
}
