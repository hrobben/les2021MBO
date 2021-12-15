<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $products = $productRepository->findAll();
        } else {
            if ($this->isGranted('ROLE_OBER')) {
                $products = $productRepository->findBy(['category' => 1]);
            } else {
                $products = $productRepository->findMultipleCats([2,3]);
            }
        }

        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/kok', name: 'product_kok', methods: ['GET'])]
    public function kok(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findBy(['category' => 2]);
        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/{slug}', name: 'product_drank', methods: ['GET'])]
    public function drank(ProductRepository $productRepository, CategoryRepository $categoryRepository, string $slug): Response
    {
        $drank = $categoryRepository->findBy(['description' => $slug]); // in $drank komt het id van description "Drank" te staan.
        if ($drank == null) {
            return $this->redirectToRoute('category_index');
        }
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findBy(['category' => $drank]),
        ]);
    }

    #[Route('/new', name: 'product_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product): Response
    {
        if ($this->isGranted("ROLE_ADMIN")) {
            $form = $this->createForm(ProductType::class, $product);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->getDoctrine()->getManager()->flush();

                return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->renderForm('product/edit.html.twig', [
                'product' => $product,
                'form' => $form,
            ]);
        } else {
            return $this->renderForm('messages/noAccess.html.twig');
        }
    }

    #[Route('/{id}', name: 'product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
    }
}
