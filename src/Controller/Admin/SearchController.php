<?php

namespace App\Controller\Admin;

use App\Service\SearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'search')]
    public function search(Request $request, SearchService $searchService): Response
    {
        $query = $request->query->get('q', '');
        $results = $searchService->search($query);

        return $this->render('page/search_results.html.twig', [
            'query' => $query,
            'results' => $results,
        ]);
    }

    #[Route('/search/debug', name: 'search_debug')]
    public function debug(Request $request): Response
    {
        $query = $request->query->get('q');
        return $this->render('search/index.html.twig', ['query' => $query]);
    }
}
