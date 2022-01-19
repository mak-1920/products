<?php

declare(strict_types=1);

namespace App\Services\Import\Savers;

use App\Entity\Import;
use App\Entity\Product;
use App\Repository\ImportRepository;
use App\Repository\ProductRepository;
use App\Services\Import\ImportRequest;

class MySQLSaver implements Saver
{
    /** @var Product[] $requests */
    private array $products;

    public function __construct(
        private ProductRepository $productRepository,
    )
    {
    }
    
    /** @var ImportRequest $request */
    public function Save(array $requests): void
    {
        $this->requestsInfo = [];

        $this->setProducts($requests);
        $this->setRequestsForProducts($requests);

        $this->productRepository->saveProducts($this->products);
    }

    private function setProducts(array $requests) : void
    {
        $products = $this->getProducts($requests);

        /** @var Product $product */
        foreach($products as $product) {
            $productName = $product->getName();
            $this->products[$productName] = $product;
        }
    }

    private function setRequestsForProducts(array $requests) : void
    {
        /** @var ImportRequest $request */
        foreach($requests as $request) {
            $productName = $request->getProduct();
            new Import(
                $this->products[$productName],
                $request->getCost(),
                $request->getCount()
            );
        }
    }

    private function getProducts(array $requests) : array
    {
        $existsProducts = $this->getExistsProducts($requests);
        $newProducts = $this->getNewProducts($existsProducts);

        $products = array_merge(
            $existsProducts, 
            $newProducts
        );

        return $products;
    }

    private function getExistsProducts(array $requests) : array
    {
        $productsNames = $this->getProductsNamesFromRequests($requests);

        $exists = $this->productRepository->getExistsProducts($productsNames);
        return $exists;
    }

    private function getNewProducts(array $existsProducts) : array
    {
        $newProducts = $this->getNotExistsProducts($existsProducts);

        $products = $this->createNewProducts($newProducts);

        return $products;
    }

    private function createNewProducts(array $products) : array
    {
        $productsEnt = [];

        foreach($products as $product) {
            $productsEnt[] = new Product($product);
        }

        return $productsEnt;
    }

    private function getNotExistsProducts(array $existsProducts) : array
    {
        $notExists = array_diff($this->productsNames, $existsProducts);

        return $notExists;
    }

    private function getProductsNamesFromRequests($products) : array
    {
        $productsNames = [];

        /** @var Product $product */
        foreach($products as $product) {
            $productsNames[] = $product->getName();
        }

        return $productsNames;
    }
}