<?php

declare(strict_types=1);

namespace App\Services\Import\Transform;

use App\Services\Currency\CurrencyProviderInterface;
use App\Services\Currency\Exceptions\ConvertException;
use App\Services\Import\Exceptions\Transform\ConverterException;
use Port\Exception;
use Port\Reader\ArrayReader;
use Port\Steps\Step\ConverterStep;
use Port\Steps\StepAggregator;
use Port\Writer\ArrayWriter;

class CostConverter implements TransformInterface
{
    public function __construct(
        private CurrencyProviderInterface $provider,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function transform(array $rows): array
    {
        $transporter = new StepAggregator(new ArrayReader($rows));
        $transporter->addWriter(new ArrayWriter($rows));
        $transporter->addStep($this->getConverter());
        $transporter->process();

        return $rows;
    }

    /**
     * @return ConverterStep
     */
    private function getConverter(): ConverterStep
    {
        $converter = new ConverterStep();
        $converter->add(fn (array $el) => $this->convertCosts($el));

        return $converter;
    }

    /**
     * @param string[] $el
     *
     * @return string[]
     *
     * @throws ConverterException
     */
    private function convertCosts(array $el): array
    {
        $costStr = $el['Cost in GBP'];
        $costParse = [];
        if (!preg_match('/^(\d+(?:\.\d{2})?)\s?(\w+)?$/i', $costStr, $costParse)) {
            return $el;
        }
        if (!array_key_exists(2, $costParse)) {
            return $el;
        }
        try {
            $el['Cost in GBP'] = (string) $this->provider->convert($costParse[2], 'USD', floatval($costParse[1]));
        } catch (ConvertException $e) {
            throw new ConverterException('Can\'t get valid currencies from CBR', previous: $e);
        }

        return $el;
    }
}
