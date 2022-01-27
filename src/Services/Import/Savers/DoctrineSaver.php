<?php

declare(strict_types=1);

namespace App\Services\Import\Savers;

use App\Entity\ProductData;
use App\Repository\ProductDataRepository;
use App\Services\Import\ImportRequest;
use DateTime;
use Port\Steps\StepAggregator;

class DoctrineSaver implements Saver
{
    /** @var ProductData[] $products */
    private array $products;

    /** @var ImportRequest[] $requests */
    private array $requests;

    /** @var string[] $productsNames */
    private array $productsNames;

    public function __construct(
        private ProductDataRepository $productRepository,
    ) {
        $this->products = [];
    }

    /**
     * @param StepAggregator[] $transporters
     *
     * @return string[][] success
     */
    public function save(array $transporters): array
    {
//        $this->requests = $requests;
//        $this->setProductNames($requests);
//
//        $this->setInvalidProductsWithExistsProductCode();
//        $this->setDiscontinued();
//
//        $this->setProducts();
//
//        $this->productRepository->saveProducts($this->products);
        $success = [];

        foreach ($transporters as $transporter) {
            $success[] = array_merge($success, $transporter->process());
        }

        return $success;
    }

    /**
     * @param ImportRequest[] $requests
     *
     * @return void
     */
    private function setProductNames(array $requests): void
    {
        $this->productsNames = $this->getProductsFieldsByObjMethod($this->requests, 'getProductName');
    }

    /**
     * @return void
     */
    private function setInvalidProductsWithExistsProductCode(): void
    {
        $codes = $this->getProductsFieldsByObjMethod($this->requests, 'getProductCode');
        $existsCodes = $this->productRepository->getExistsProductCodes($codes);

        foreach ($this->requests as $request) {
            if ($request->getIsValid()) {
                if (in_array($request->getProductCode(), $existsCodes)) {
                    $request->setIsValid(false);
                } else {
                    $existsCodes[] = $request->getProductCode();
                }
            }
        }
    }

    /**
     * @return void
     */
    private function setDiscontinued(): void
    {
        $names = $this->getProductsFieldsByObjMethod($this->requests, 'getProductName');
        $discontinuedProducts = $this->productRepository->getDiscontinuedProductsByNames($names);
        $discontinuedProducts = $this->transformDiscontinuedArr($discontinuedProducts);

        foreach ($this->requests as $request) {
            if ($request->getIsValid()) {
                if (array_key_exists($request->getProductName(), $discontinuedProducts)) {
                    $request->setDiscontinuedDate($discontinuedProducts[$request->getProductName()]);
                } elseif ($request->getDiscontinued()) {
                    $discontinuedProducts[$request->getProductName()] = $request->getDiscontinuedDate();
                }
            }
        }
    }

    /**
     * @param array{dtmdiscontinued: DateTime, strproductname: string} $array
     *
     * @return array<string, DateTime>
     */
    private function transformDiscontinuedArr(array $array): array
    {
        $result = [];

        for ($i = 0; $i < count($array); ++$i) {
            $result[$array[$i]['strproductname']] = $array[$i]['dtmdiscontinued'];
        }

        return $result;
    }

    /**
     * @return void
     */
    private function setProducts(): void
    {
        $products = $this->getProducts();

        foreach ($products as $product) {
            $this->products[] = $product;
        }
    }

    /**
     * @return ProductData[]
     */
    private function getProducts(): array
    {
        $products = [];

        foreach ($this->requests as $request) {
            if ($request->getIsValid()) {
                $products[] = $this->createProduct($request);
            }
        }

        return $products;
    }

    /**
     * @param ImportRequest $request
     *
     * @return ProductData
     */
    private function createProduct(ImportRequest $request): ProductData
    {
        $product = new ProductData();

        $product->setStrproductcode($request->getProductCode());
        $product->setStrproductname($request->getProductName());
        $product->setStrproductdesc($request->getProductDesc());
        $product->setStock($request->getStock());
        $product->setCost($request->getCost());
        if ($request->getDiscontinued()) {
            $product->setDtmdiscontinued(
                $request->getDiscontinuedDate() ?? new DateTime()
            );
        }

        return $product;
    }

    /**
     * @param ImportRequest[] $rows
     * @param string $methodName
     *
     * @return string[]
     */
    private function getProductsFieldsByObjMethod(array $rows, string $methodName): array
    {
        $result = [];

        foreach ($rows as $row) {
            if ($row->getIsValid()) {
                $result[] = $row->$methodName();
            }
        }

        return $result;
    }
}
