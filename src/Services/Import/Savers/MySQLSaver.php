<?php

declare(strict_types=1);

namespace App\Services\Import\Savers;

use App\Repository\TblproductdataRepository;
use App\Services\Import\ImportRequest;
use App\Entity\Tblproductdata;
use App\Entity\Request;
use DateTime;
use Symfony\Component\PropertyAccess\Exception\UninitializedPropertyException;

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
        $this->productsNames = $this->getProductsFieldsByObjMethod($this->requests, 'getProductName', false);

        $this->setInvalidProductsWithExistsProductCode();
        $this->setDiscontinued();

        $this->setProducts();

        $this->productRepository->saveProducts($this->products);
    }

    private function setInvalidProductsWithExistsProductCode() : void
    {
        $codes = $this->getProductsFieldsByObjMethod($this->requests, 'getProductCode', true);
        $existsCodes = $this->productRepository->getExistsProductCodes($codes);

        foreach($this->requests as $request) {
            if($request->getIsValid()) {
                if(array_search($request->getProductCode(), $existsCodes) !== false) {
                    $request->setIsValid(false);
                } else {
                    $existsCodes[] = $request->getProductCode();
                }
            }
        }
    }

    private function setDiscontinued() : void
    {
        $names = $this->getProductsFieldsByObjMethod($this->requests, 'getProductName', true);
        $discontinuedProducts = $this->productRepository->getDiscontinuedProductsByNames($names);
        $discontinuedProducts = $this->transformDiscontinuedArr($discontinuedProducts);

        foreach($this->requests as $request) {
            if($request->getIsValid()) {
                if(array_key_exists($request->getProductName(), $discontinuedProducts)) {
                    $request->setDiscontinuedDate($discontinuedProducts[$request->getProductName()]);
                }
            }
        }
    }

    private function transformDiscontinuedArr(array $array) : array
    {
        $result = [];

        for($i = 0; $i < count($array); $i++) {
            $result[$array[$i]['strproductname']] = $array[$i]['dtmdiscontinued'];
        }

        return $result;
    }
    
    private function setProducts() : void
    {
        $products = $this->getProducts();

        /** @var Tblproductdata $product */
        foreach($products as $product) {
            $this->products[] = $product;
        }
    }

    private function getProducts() : array
    {
        $products = [];

        foreach($this->requests as $request) {
            if($request->getIsValid()) {
                $products[] = $this->createProduct($request);
            }
        }

        return $products;
    }

    private function createProduct(ImportRequest $request) : Tblproductdata
    {
        $product = new Tblproductdata();

        $product->setStrproductcode($request->getProductCode());
        $product->setStrproductname($request->getProductName());
        $product->setStrproductdesc($request->getProductDecs());
        $product->setStock($request->getStock());
        $product->setCost($request->getCost());
        if($request->getDiscontinued()) {
            $product->setDtmdiscontinued(
                $request->getDiscontinuedDate() ?? new DateTime()
            );
        }

        return $product;
    }

    private function getProductsFieldsByObjMethod(array $rows, string $methodName, bool $checkValid) : array
    {
        $result = [];

        /** @var ImportRequest $row */
        foreach($rows as $row) {
            if($checkValid && $row->getIsValid()) {
                $result[] = $row->$methodName();
            }
        }
        return $result;
    }
}