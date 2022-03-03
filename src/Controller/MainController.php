<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ImportByCSVType;
use App\Repository\ImportStatusRepository;
use App\Repository\ProductDataRepository;
use App\Services\Import\Exceptions\Status\UndefinedStatusIdException;
use App\Services\Import\Statuses\DoctrineStatus;
use App\Services\Paginator\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
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
        Paginator $paginator,
        ProductDataRepository $repository,
    ): Response {
        $lastId = (int) $request->query->get('last', $repository->getLastProductId());
        $page = (int) $request->query->get('page', 1);
        $query = $repository->getQueryForTakeAllProducts($lastId);

        $pagination = $paginator->paginate($page, $lastId, $query, 10);

        return $this->render('main/products.html.twig', [
            'page' => $page,
            'products' => $pagination,
        ]);
    }

    #[Route(
        '/requests',
        name: 'requests_view',
    )]
    public function viewRequests(
        Request $request,
        Paginator $paginator,
        ImportStatusRepository $repository
    ): Response {
        $lastId = (int) $request->query->get('last', $repository->getLastStatusId());
        $page = (int) $request->query->get('page', 1);
        $query = $repository->getQueryForTakeAllStatuses($lastId);

        $pagination = $paginator->paginate($page, $lastId, $query, 20);

        return $this->render('main/requests.html.twig', [
            'page' => $page,
            'requests' => $pagination,
        ]);
    }

    #[Route(
        '/status-{id}',
        name: 'status_page',
        options: [
            'expose' => true,
        ],
    )]
    public function statusPage(
        int $id,
        DoctrineStatus $doctrineStatus,
        Session $session,
    ): Response {
        try {
            $status = $doctrineStatus->getStatus($id);
        } catch (UndefinedStatusIdException) {
            throw $this->createNotFoundException('Request with id '.$id.' not found');
        }

        $sessionKey = 'status:views:'.$id;
        if (!$session->has($sessionKey)) {
            $session->set($sessionKey, 1);
        } else {
            $session->set($sessionKey, $session->get($sessionKey) + 1);
        }

        return $this->render(
            'main/status.html.twig',
            [
                'status' => $status,
                'views' => $session->get($sessionKey),
            ]
        );
    }
}
