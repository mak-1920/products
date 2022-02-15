<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ImportByCSVType;
use App\Repository\ProductDataRepository;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'products_create')]
    public function index(
    ): Response {
        $form = $this->createForm(ImportByCSVType::class);

        return $this->renderForm('main/upload.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(
        '/products',
        name: 'products_view',
    )]
    public function viewProducts(
        Request $request,
        PaginatorInterface $paginator,
        ProductDataRepository $repository,
    ): Response {
        $lastId = (int) $request->get('last', $repository->getLastProductId());
        $page = (int) $request->get('page', 1);

        $query = $repository->getQueryForTakeAllProducts($lastId);

        /** @var SlidingPagination $pagination */
        $pagination = $paginator->paginate(
            $query,
            $page,
            10
        );
        $pagination->setParam('last', $lastId);

        return $this->render('main/products.html.twig', [
            'page' => $page,
            'products' => $pagination,
        ]);
    }
}
