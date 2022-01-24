<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ImportByCSVType;
use App\Services\Import\CSV\CSVSettings;
use App\Services\Import\CSV\ImportCSV;
use App\Services\Import\Savers\MySQLSaver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'main')]
    public function index(Request $request, MySQLSaver $saver): Response
    {
        $import = null;

        $form = $this->createForm(ImportByCSVType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $import = new ImportCSV(
                $request->files->get('import_by_csv')['file']->getPathname(),
                new CSVSettings(
                    $form->get('delimiter')->getData() ?? ' ',
                    $form->get('enclosure')->getData() ?? ' ',
                    $form->get('escape')->getData() ?? ' ',
                    $form->get('haveHeader')->getData()
                ),
                $form->get('testmode')->getData(),
                $saver
            );
            $import->SaveRequests();
        }

        return $this->renderForm('main/index.html.twig', [
            'form' => $form,
            'import' => $import,
        ]);
    }
}
