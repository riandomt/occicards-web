<?php

namespace App\Controller;

use App\Repository\FolderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FolderController extends AbstractController
{
    #[Route('/folder', name: 'app_folder_index')]
    public function index(FolderRepository $em): Response
    {
        $user = $this->getUser();

        $folders = $em->findBy(
            ['user' => $user],
            ['parent' => 'ASC', 'name' => 'ASC']
        );
        return $this->render('folder/index.html.twig', [
            'controller_name' => 'FolderController',
            'folders' => $folders
        ]);
    }

    #[Route('/folder/{id}/delete', name: 'app_folder_delete')]

    public function delete()
    {

    }
}
