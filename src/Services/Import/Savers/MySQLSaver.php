<?php

declare(strict_types=1);

namespace App\Services\Import\Savers;

use App\Entity\ProductData;
use App\Repository\ProductDataRepository;
use App\Services\Import\ImportRequest;
use DateTime;

class MySQLSaver implements Saver
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
    }

    /**
     * @param ImportRequest[] $requests
     *
     * @return void
     */
    public function Save(array $requests): void
    {
        $this->products = [];
        $this->requests = $requests;
        $this->productsNames = $this->getProductsFieldsByObjMethod($this->requests, 'getProductName');

        $this->setInvalidProductsWithExistsProductCode();
        $this->setDiscontinued();

        $this->setProducts();

        $this->productRepository->saveProducts($this->products);
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
                if (false !== array_search($request->getProductCode(), $existsCodes)) {
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

        /** @var ProductData $product */
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
