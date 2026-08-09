<?php

namespace App\Controller;

use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

/**
 * Controller for trustindex home task.
 *
 * @author Attila HOFFEREK <azhofi@gmail.com>
 */
#[Route('/')]
final class MainController extends AbstractController
{
    #[Route('/', name: 'review_index', defaults: ['page' => '1', '_format' => 'html'], methods: ['GET'])]
    #[Route('/page/{page}', name: 'review_index_paginated', defaults: ['_format' => 'html'], requirements: ['page' => Requirement::POSITIVE_INT], methods: ['GET'])]
    public function index(Request $request, int $page, string $_format, ReviewRepository $reviews): Response
    {
        $latestReviews = $reviews->findLatest($page);

        return $this->render('review/index.'.$_format.'.twig', [
            'paginator' => $latestReviews
        ]);
    }

    #[Route('/new', name: 'review_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($review);
            $entityManager->flush();

            $this->addFlash('success', 'Köszönjük a véleményed!');

            return $this->redirectToRoute('review_new');
        }

        return $this->render('review/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
 
    #[Route('/show/{id}', name: 'review_show', methods: ['GET'])]
    public function show(Review $review): Response
    {
        return $this->render('review/show.html.twig', [
            'review' => $review,
        ]);
    }
    
    #[Route('/edit/{id}', name: 'review_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Review $review, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReviewType::class, $review);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Az értékelés sikeresen frissítve lett!');

            return $this->redirectToRoute('review_show', [
                'id' => $review->getId(),
            ]);
        }

        return $this->render('review/edit.html.twig', [
            'form' => $form->createView(),
            'review' => $review,
        ]);
    }
    
    #[Route('/search', name: 'review_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        return $this->render('review/search.html.twig', ['query' => (string) $request->query->get('q', '')]);
    }
    
    #[Route('/companies', name: 'company_statistics', methods: ['GET'])]
    public function companyStatistics(ReviewRepository $reviewRepository): Response
    {
        $stats = $reviewRepository->getCompanyStatistics();

        return $this->render('review/company_statistics.html.twig', [
            'stats' => $stats,
        ]);
    }
}
