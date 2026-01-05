<?php

namespace App\Controller\Inventory;

use App\Repository\InventoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/public/inventory')]
final class PublicInventoryController extends AbstractController
{
    #[Route('', name: 'public_inventory_index', methods: ['GET'])]
    public function index(
        InventoryRepository $inventoryRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $query = $inventoryRepository->createQueryBuilder('i')
            ->where('i.isPublic = true')
            ->orderBy('i.id', 'DESC')
            ->getQuery();
        
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );
        
        return $this->render('public_inventory/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }
}