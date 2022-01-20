<?php

declare(strict_types=1);

namespace App\Services\Import\Savers;

use App\Entity\Import;
use App\Entity\Product;
use App\Repository\ImportRepository;
use App\Repository\ProductRepository;
use App\Repository\TblproductdataRepository;
use App\Services\Import\ImportRequest;

class MySQLSaver implements Saver
{
    /** @var Product[] $requests */
    private array $products;

    /** @var ImportRequest[] $requests */
    private array $requests;

    /** @var string[] $productsNames */
    private array $productsNames;

    public function __construct(
        private TblproductdataRepository $productRepository,
    )
    {
    }
    
    /** @var ImportRequest $request */
    public function Save(array $requests): void
    {
        $this->products = [];
        $this->requests = $requests;
        $this->productsNames = $this->getProductsNamesByObjMethod($this->requests, 'getProduct');

        $this->setProducts();
        $this->setRequestsForProducts();

        $this->productRepository->saveProducts($this->products);
    }

    private function setProducts() : void
    {
        $products = $this->getProducts();

        /** @var Product $product */
        foreach($products as $product) {
            $productName = $product->getName();
            $this->products[$productName] = $product;
        }
    }

    private function setRequestsForProducts() : void
    {
        // /** @var ImportRequest $request */
        // foreach($this->requests as $request) {
        //     $productName = $request->getProduct();
        //     /** @var Product $product */
        //     $product = $this->products[$productName];
            
        //     $import = Import::Create(
        //         $product,
        //         $request->getCost(),
        //         $request->getCount()
        //     );

        //     $product->addImport($import);
        // }
    }

    private function getProducts() : array
    {
        $existsProducts = $this->getExistsProducts();
        $newProducts = $this->getNewProducts($existsProducts);

        $products = array_merge(
            $existsProducts, 
            $newProducts
        );

        return $products;
    }

    private function getExistsProducts() : array
    {
        $exists = $this->productRepository->getExistsProducts($this->productsNames);
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

        // foreach($products as $product) {
        //     $productEnt = new Product();
        //     $productEnt->setName($product);
        //     $productsEnt[] = $productEnt;
        // }

        return $productsEnt;
    }

    private function getNotExistsProducts(array $existsProducts) : array
    {
        $existsProductsNames = $this->getProductsNamesByObjMethod($existsProducts, 'getName');
        
        $notExists = array_diff($this->productsNames, $existsProductsNames);

        return $notExists;
    }

    private function getProductsNamesByObjMethod(array $requests, string $methodName) : array
    {
        $productsNames = [];

        /** @var ImportRequest $request */
        foreach($requests as $request) {
            $productsNames[] = $request->$methodName();
        }

        return $productsNames;
    }
}