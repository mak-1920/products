<?php

declare(strict_types=1);

namespace App\Controller\Ajax;

use App\Services\FilesManagers\TempFilesManager;
use App\Services\Import\Senders\MercureSender;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImportController extends AbstractController
{
    #[Route(
        '/ajax/import',
        name: 'ajax_import_upload',
        options: ['expose' => true],
        methods: 'post',
    )]
    public function upload(
        Request $request,
        MercureSender $sender,
        TempFilesManager $filesManager,
    ): Response {
        if (0 === count($request->files) || is_null($request->get('settings'))) {
            return $this->json(['ids' => ''], Response::HTTP_NO_CONTENT);
        }

        $files = $filesManager->saveFilesAndGetInfo($request->files->get('files'));
        $settings = $request->get('settings');
        $token = $request->get('token');

        $ids = $sender->send(
            $files,
            $settings,
            $token,
        );

        return $this->json(['ids' => $ids], Response::HTTP_ACCEPTED);
    }
}
