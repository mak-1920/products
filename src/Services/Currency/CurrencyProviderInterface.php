<?php

declare(strict_types=1);

namespace App\Services\Currency;

use App\Services\Currency\Converters\CurrencyConverterInterface;

interface CurrencyProviderInterface extends CurrencyConverterInterface, CurrenciesNamesInterface
{
}
