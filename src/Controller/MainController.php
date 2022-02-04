<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ImportByCSVType;
use App\Services\Import\Sender;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'main')]
    public function index(
        Request $request,
        Sender $sender,
    ): Response {
        $form = $this->createForm(ImportByCSVType::class);
        $form->handleRequest($request);
        $ids = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $ids = $sender->send(
                $request->files->get('import_by_csv')['file'],
                $form->get('csvSettings')->getData(),
            );
        }

        return $this->renderForm('main/index.html.twig', [
            'form' => $form,
            'ids' => $ids,
        ]);
    }
}
