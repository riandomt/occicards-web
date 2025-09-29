<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Entity\Folder;
use App\Entity\User;
use App\Form\DeckType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DeckController extends AbstractController
{
    #[Route('/deck', name: 'app_deck_show', methods:['GET'])]
    public function show(): Response
    {
        return $this->render('deck/index.html.twig', [
            'controller_name' => 'DeckController',
        ]);
    }

    #[Route('/deck/create', name: 'app_deck_create', methods:['GET'])]
    public function create(): Response
    {
        $form = $this->createForm(DeckType::class, new Deck());

        return $this->render('deck/create.html.twig', [
            'controller_name' => 'DeckController',
            'form' => $form->createView()
        ]);
    }

#[Route('/deck/store', name:'app_deck_store', methods:['POST'])]
public function store(Request $request, EntityManagerInterface $em): Response
{
    $deck = new Deck();
    $form = $this->createForm(DeckType::class, $deck);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // User = toujours l’utilisateur connecté
        $deck->setUser($this->getUser());

        // Folder : récupéré via champ hidden ou logique métier
        $parentId = $form->get('parentId')->getData();
        if ($parentId) {
            $folder = $em->getRepository(Folder::class)->find($parentId);
            $deck->setFolder($folder);
        }

        $deck->setCreatedAt(new DateTimeImmutable());
        $deck->setUpdatedAt(new DateTimeImmutable());

        $em->persist($deck);
        $em->flush();

        $this->addFlash('success', 'Le deck a été créé avec succès.');

        return $this->redirectToRoute('app_folder_index');
    }

    foreach ($form->getErrors(true) as $error) {
        $this->addFlash('error', $error->getMessage());
    }

    return $this->redirectToRoute('app_deck_create');
}

}
