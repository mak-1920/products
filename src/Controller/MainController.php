<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ImportByCSVType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'main')]
    public function index(Request $request): Response
    {
        $importCSVForm = $this->createForm(ImportByCSVType::class);

        $importCSVForm->handleRequest($request);

        if($importCSVForm->isSubmitted() && $importCSVForm->isValid()) {
            var_dump($importCSVForm);
        }

        return $this->renderForm('main/index.html.twig', [
            'form' => $importCSVForm,
        ]);
    }
}
