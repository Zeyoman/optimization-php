<?php

namespace App\Controller;

use App\Repository\DirectusFilesRepository;
use App\Repository\GalaxyRepository;
use App\Repository\ModelesFilesRepository;
use App\Repository\ModelesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpCache\Annotation\Cache;
use Symfony\Component\Routing\Attribute\Route;

final class CarouselController extends AbstractController
{

    #[Route('/carousel', name: 'app_carousel')]
    public function index(GalaxyRepository $galaxyRepository, ModelesRepository $modelesRepository, ModelesFilesRepository $modelesFilesRepository, DirectusFilesRepository $directusFilesRepository): Response
    {
        $galaxies = $galaxyRepository->findAll();

        $modelIds = [];
        foreach ($galaxies as $galaxy) {
            $modelIds[] = $galaxy->getModele();
        }
        $modelIds = array_unique($modelIds);

        $modelesFiles = $modelesFilesRepository->findBy([
            'modeles_id' => $modelIds
        ]);

        $filesByModel = [];
        $directusFileIds = [];
        foreach ($modelesFiles as $modelesFile) {
            $filesByModel[$modelesFile->getModelesId()][] = $modelesFile;
            $directusFileIds[] = $modelesFile->getDirectusFilesId();
        }
        $directusFileIds = array_unique($directusFileIds);

        $directusFiles = $directusFilesRepository->findBy([
            'id' => $directusFileIds
        ]);

        $directusFilesById = [];
        foreach ($directusFiles as $directusFile) {
            $directusFilesById[$directusFile->getId()] = $directusFile;
        }

        $carousel = [];
        foreach ($galaxies as $galaxy) {
            $modelId = $galaxy->getModele();
            $files = [];

            if (isset($filesByModel[$modelId])) {
                foreach ($filesByModel[$modelId] as $modelesFile) {
                    $directusFileId = $modelesFile->getDirectusFilesId();
                    if (isset($directusFilesById[$directusFileId])) {
                        $files[] = $directusFilesById[$directusFileId];
                    }
                }
            }

            $carousel[] = [
                'title'       => $galaxy->getTitle(),
                'description' => $galaxy->getDescription(),
                'files'       => $files,
            ];
        }

        return $this->render('carousel/index.html.twig', [
            'carousel' => $carousel,
        ]);
    }
}
