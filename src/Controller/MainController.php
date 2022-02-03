<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ImportByCSVType;
use App\Services\Import\Logger\Logger;
use App\Services\Import\TempFilesManager;
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
        Logger $logger,
        TempFilesManager $filesManager,
    ): Response {
        $form = $this->createForm(ImportByCSVType::class);
        $form->handleRequest($request);
        $ids = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $files = $request->files->get('import_by_csv')['file'];
            $filesInfo = $filesManager->saveFiles($files);
            $ids = $logger->createStatuses([
                'files' => $filesInfo,
                'settings' => $form->get('csvSettings')->getData(),
            ]);
            $producer->sendIDs($ids);
        }

        return $this->renderForm('main/index.html.twig', [
            'form' => $form,
            'ids' => $ids,
        ]);
    }
}
