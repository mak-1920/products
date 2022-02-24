<?php

declare(strict_types=1);

namespace App\Services\Import\Statuses;

use App\Services\Import\Loggers\LoggingInterface;

interface LoggingStatusInterface extends LoggingInterface, StatusInterface
{
}
