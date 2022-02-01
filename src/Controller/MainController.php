<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ImportByCSVType;
use App\Services\RabbitMQ\Import\MessageSerializer;
use App\Services\RabbitMQ\Import\SendProducer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'main')]
    public function index(
        Request $request,
        SendProducer $producer,
        MessageSerializer $messageSerializer
    ): Response {
        $form = $this->createForm(ImportByCSVType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $msg = $messageSerializer->serialize([
                'files' => $request->files->get('import_by_csv')['file'],
                'settings' => $form->get('csvSettings')->getData(),
                'testmode' => $form->get('testmode')->getData(),
            ]);
            $producer->send($msg);
        }

        return $this->renderForm('main/index.html.twig', [
            'form' => $form,
        ]);
    }
}
