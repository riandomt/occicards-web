<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Form\DeckType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DeckController extends AbstractController
{
    #[Route('/deck', name: 'app_deck_show')]
    public function show(): Response
    {
        return $this->render('deck/index.html.twig', [
            'controller_name' => 'DeckController',
        ]);
    }

    #[Route('/deck/create', name: 'app_deck_create')]
    public function create(): Response
    {
        $form = $this->createForm(DeckType::class, new Deck());

        return $this->render('deck/create.html.twig', [
            'controller_name' => 'DeckController',
            'form' => $form->createView()
        ]);
    }
}
