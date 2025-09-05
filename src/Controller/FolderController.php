<?php

namespace App\Controller;

use App\Entity\Folder;
use App\Entity\User;
use App\Form\FolderFormType;                  // <-- important
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
    #[Route('/folder/{id}', name: 'app_folder_index', requirements: ['id' => '\d+'], defaults: ['id' => null])]
    public function index(
        ?int $id,
        FolderRepository $repo,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $current = null;

        if ($id) {
            $candidate = $em->getRepository(Folder::class)->find($id);
            if ($candidate && $candidate->getUser()?->getId() === $user->getId()) {
                $current = $candidate;
            }
        }

        if (!$current) {
            // fallback : dossier /home de l’utilisateur
            $current = $repo->findHomeForUser($user);
        }

        $path = $current ? $current->getPath() : '/';
        
        $form = $this->createForm(FolderFormType::class, new Folder());

        return $this->render('folder/index.html.twig', [
            'current' => $current,
            'folders' => $current?->getChildren() ?? [],
            'path' => $path,
            'form' => $form->createView(),
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

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('error', 'Formulaire invalide.');
            return $this->redirectToRoute('app_folder_index');
        }

        // parent_id (hidden non mappé) -> entité
        $parentId = $form->get('parent_id')->getData();
        if ($parentId) {
            $parent = $folderRepo->find((int) $parentId);
            if ($parent && $parent->getUser()?->getId() === $user->getId()) {
                $folder->setParent($parent);
            }
        }

        // user_id (si tu décides de t'en servir) sinon getUser()
        $folder->setUser($user);

        $now = new DateTimeImmutable();
        $folder->setCreatedAt($now);
        $folder->setUpdatedAt($now);

        $em->persist($folder);
        $em->flush();

        return $this->redirectToRoute('app_folder_index', ['id' => $folder->getId()]);
    }

    #[Route('/folder/{id}/update', name: 'app_folder_update', methods: ['POST', 'PUT'])]
    public function update(
        Request $request,
        Folder $folder,                               // injection par {id}
        FolderRepository $folderRepo,
        EntityManagerInterface $em
    ): RedirectResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }
        if ($folder->getUser()?->getId() !== $user->getId()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(FolderFormType::class, $folder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // parent_id (hidden non mappé)
            $parentId = $form->get('parent_id')->getData();
            if ($parentId) {
                $parent = $folderRepo->find((int) $parentId);
                if ($parent && $parent->getUser()?->getId() === $user->getId()) {
                    $folder->setParent($parent);
                }
            }

            $folder->setUpdatedAt(new DateTimeImmutable());
            $em->flush();
        } else {
            $this->addFlash('error', 'Formulaire invalide.');
        }

        return $this->redirectToRoute('app_folder_index', ['id' => $folder->getId()]);
    }

    #[Route('/folder/{id}/delete', name: 'app_folder_delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, Folder $folder, FolderRepository $repo, EntityManagerInterface $em): RedirectResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }
        if ($folder->getUser()?->getId() !== $user->getId()) {
            throw $this->createNotFoundException();
        }

        // CSRF
        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_folder_' . $folder->getId(), $token)) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        // (Optionnel) empêcher la suppression du /home
        $home = $repo->findHomeForUser($user);
        if ($home && $home->getId() === $folder->getId()) {
            $this->addFlash('warning', 'Vous ne pouvez pas supprimer /home.');
            return $this->redirectToRoute('app_folder_index', ['id' => $folder->getId()]);
        }

        $parent = $folder->getParent();
        $em->remove($folder);
        $em->flush();

        return $this->redirectToRoute('app_folder_index', ['id' => $parent?->getId()]);
    }
}
