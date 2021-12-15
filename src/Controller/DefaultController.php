<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Repository\BlogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// Include Dompdf required namespaces
use Dompdf\Dompdf;
use Dompdf\Options;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'default')]
    public function index(Request $request, BlogRepository $blogRepository): Response
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $blogs = $blogRepository->findBySearch($form->getViewData()['zoeken']);
            return $this->render('blog/index.html.twig', [
                'blogs' => $blogs,
            ]);
        }
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
            'sform' => $form->createView(),
        ]);
    }
}
