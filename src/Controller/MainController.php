<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ImportByCSVType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'main')]
    public function index(
    ): Response {
        $form = $this->createForm(ImportByCSVType::class);

        return $this->renderForm('main/index.html.twig', [
            'form' => $form,
        ]);
    }
}
