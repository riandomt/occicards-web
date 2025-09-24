<?php

namespace App\Controller;

use App\Entity\Folder;
use App\Entity\User;
use App\Form\FolderType;
use App\Repository\FolderRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FolderController extends AbstractController
{
    #[Route('/folder', name: 'app_folder_index', methods: ['GET'])]
    public function index(FolderRepository $repo): Response
    {
        $user = $this->getUser();
        $homeFolder = $repo->findHomeByUser($user);

        if (!$homeFolder) {
            throw $this->createNotFoundException("Pas de dossier home trouvé.");
        }

        return $this->redirectToRoute('app_folder_show', [
            'id' => $homeFolder->getId()
        ]);
    }


    #[Route('/folder/{id}/show', name: 'app_folder_show', methods: ['GET'])]
    public function show(int $id, FolderRepository $repo): Response
    {
        $user = $this->getUser();
        $folder = $repo->findWithChildren($id);
        $children = $folder->getChildren();

        $createForm = $this->createForm(FolderType::class, new Folder());

        $updateForms = [];
        foreach ($children as $child) {
            $updateForms[$child->getId()] = $this->createForm(FolderType::class, $child)->createView();
        }

        return $this->render('folder/show.html.twig', [
            'controller_name' => 'FolderController',
            'user' => $user,
            'folder' => $folder,
            'children' => $children,
            'createForm' => $createForm->createView(),
            'updateForms' => $updateForms
        ]);
    }

    #[Route('/folder/store', name: 'app_folder_store', methods: ['POST'])]
    public function store(Request $request, EntityManagerInterface $em)
    {
        $folder = new Folder();
        $form = $this->createForm(FolderType::class, $folder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $parentId = $form->get('parentId')->getData();
            $userId = $form->get('userId')->getData();

            if ($parentId) {
                $parent = $em->getRepository(Folder::class)->find($parentId);
                $folder->setParent($parent);
            }

            if ($userId) {
                $user = $em->getRepository(User::class)->find($userId);
                $folder->setUser($user);
            }

            $folder->setCreatedAt(new DateTimeImmutable());
            $folder->setUpdatedAt(new DateTimeImmutable());

            $em->persist($folder);
            $em->flush();

        }

        return $this->redirectToRoute('app_folder_index');
    }



    #[Route('/folder/{id}/update', name: 'app_folder_update', methods: ['POST', 'PUT'])]
    public function update(Folder $folder, Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(FolderType::class, $folder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $folder->setUpdatedAt(new DateTimeImmutable());
            $em->flush();
        }

        return $this->redirectToRoute('app_folder_index');
    }

    #[Route('/folder/{id}/delete', name: 'app_folder_delete', methods: ['POST', 'DELETE'])]
    public function delete(
        Request $request,
        Folder $folder,
        EntityManagerInterface $em,
        FolderRepository $repo
    ): Response {

        if ($this->isCsrfTokenValid('delete' . $folder->getId(), $request->request->get('_token'))) {
            $em->remove($folder);
            $em->flush();
        }

        return $this->redirectToRoute('app_folder_index');
    }
}
