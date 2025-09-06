<?php
namespace App\Controller;

use App\Entity\Folder;
use App\Entity\User;
use App\Form\FolderFormType;
use App\Repository\FolderRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FolderController extends AbstractController
{
    #[Route('/folder', name: 'app_folder_index', methods: ['GET'])]
    public function index(
        FolderRepository $repo,
        EntityManagerInterface $em
    ) {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $homeFolders = $repo->findHomeByUser($user);
        if (empty($homeFolders)) {
            throw $this->createNotFoundException("Le dossier home n'existe pas.");
        }
        return $this->redirectToRoute('app_folder_show', ['id' => $homeFolders[0]->getId()]);
    }

    #[Route('/folder/{id}/show', name: 'app_folder_show', methods: ['GET'])]
    public function show(
        int $id,
        FolderRepository $repo,
        Request $request
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        // On récupère le dossier demandé
        $currentFolder = $repo->findOneBy(['id' => $id, 'user' => $user]);
        if (!$currentFolder) {
            throw $this->createNotFoundException("Dossier introuvable.");
        }

        // Structure récursive
        $folders = $repo->getFolderStructure($user, $currentFolder);

        // Formulaire de création
        $newFolder = new Folder();
        $newFolder->setUser($user);
        $newFolder->setParent($currentFolder);

        $createForm = $this->createForm(FolderFormType::class, $newFolder);

        // Formulaire de mise à jour (lié au dossier courant)
        $updateForm = $this->createForm(FolderFormType::class, $currentFolder);

        return $this->render('folder/index.html.twig', [
            'folders' => $folders['children'],
            'currentFolder' => $currentFolder,
            'createForm' => $createForm->createView(),
            'updateForm' => $updateForm->createView(),
        ]);
    }


    #[Route('/folder/store', name: 'app_folder_store', methods: ['POST'])]
    public function store(
        Request $request,
        FolderRepository $folderRepo,
        EntityManagerInterface $em
    ): RedirectResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $folder = new Folder();
        $form = $this->createForm(FolderFormType::class, $folder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du parent
            $parentId = $form->get('parent_id')->getData();
            if ($parentId) {
                $parent = $folderRepo->find($parentId);
                if ($parent && $parent->getUser() === $user) {
                    $folder->setParent($parent);
                }
            }

            // Configuration de l'utilisateur et dates
            $folder->setUser($user);
            $now = new DateTimeImmutable();
            $folder->setCreatedAt($now);
            $folder->setUpdatedAt($now);

            $em->persist($folder);
            $em->flush();

            return $this->redirectToRoute('app_folder_index', ['id' => $folder->getId()]);
        }

        $this->addFlash('error', 'Formulaire invalide.');
        return $this->redirectToRoute('app_folder_index');
    }

    #[Route('/folder/{id}/update', name: 'app_folder_update', methods: ['POST', 'PUT'])]
    public function update(
        Request $request,
        Folder $folder,
        FolderRepository $folderRepo,
        EntityManagerInterface $em
    ): RedirectResponse {
        $user = $this->getUser();
        if (!$user instanceof User || $folder->getUser() !== $user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(FolderFormType::class, $folder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du parent
            $parentId = $form->get('parent_id')->getData();
            if ($parentId) {
                $parent = $folderRepo->find($parentId);
                if ($parent && $parent->getUser() === $user) {
                    $folder->setParent($parent);
                }
            }

            $folder->setUpdatedAt(new DateTimeImmutable());
            $em->flush();

            return $this->redirectToRoute('app_folder_index', ['id' => $folder->getId()]);
        }

        $this->addFlash('error', 'Formulaire invalide.');
        return $this->redirectToRoute('app_folder_index', ['id' => $folder->getId()]);
    }

    #[Route('/folder/{id}/delete', name: 'app_folder_delete', methods: ['POST', 'DELETE'])]
    public function delete(
        Request $request,
        Folder $folder,
        FolderRepository $repo,
        EntityManagerInterface $em
    ): RedirectResponse {
        $user = $this->getUser();
        if (!$user instanceof User || $folder->getUser() !== $user) {
            return $this->redirectToRoute('app_login');
        }

        // Vérification du token CSRF
        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_folder_' . $folder->getId(), $token)) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        // Empêcher la suppression du dossier home
        $homeFolders = $repo->findHomeByUser($user);
        if (!empty($homeFolders) && $homeFolders[0]->getId() === $folder->getId()) {
            $this->addFlash('warning', 'Vous ne pouvez pas supprimer le dossier home.');
            return $this->redirectToRoute('app_folder_index');
        }

        $parent = $folder->getParent();
        $em->remove($folder);
        $em->flush();

        $redirectId = $parent ? $parent->getId() : null;
        return $this->redirectToRoute('app_folder_index', ['id' => $redirectId]);
    }
}
