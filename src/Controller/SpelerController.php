<?php

namespace App\Controller;

use App\Entity\Speler;
use App\Form\SpelerType;
use App\Repository\SpelerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/speler')]
class SpelerController extends AbstractController
{
    #[Route('/', name: 'speler_index', methods: ['GET'])]
    public function index(SpelerRepository $spelerRepository): Response
    {
        return $this->render('speler/index.html.twig', [
            'spelers' => $spelerRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'speler_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $speler = new Speler();
        $form = $this->createForm(SpelerType::class, $speler);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($speler);
            $entityManager->flush();

            return $this->redirectToRoute('speler_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('speler/new.html.twig', [
            'speler' => $speler,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'speler_show', methods: ['GET'])]
    public function show(Speler $speler): Response
    {
        return $this->render('speler/show.html.twig', [
            'speler' => $speler,
        ]);
    }

    #[Route('/{id}/edit', name: 'speler_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Speler $speler, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SpelerType::class, $speler);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('speler_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('speler/edit.html.twig', [
            'speler' => $speler,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'speler_delete', methods: ['POST'])]
    public function delete(Request $request, Speler $speler, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$speler->getId(), $request->request->get('_token'))) {
            $entityManager->remove($speler);
            $entityManager->flush();
        }

        return $this->redirectToRoute('speler_index', [], Response::HTTP_SEE_OTHER);
    }
}
