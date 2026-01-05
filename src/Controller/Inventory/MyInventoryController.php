<?php

namespace App\Controller\Inventory;

use App\Entity\Inventory;
use App\Repository\InventoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/my')]
final class MyInventoryController extends AbstractController
{
    #[Route('/inventory', name: 'my_inventory_index', methods: ['GET'])]
    public function index(
        InventoryRepository $inventoryRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $query = $inventoryRepository->createQueryBuilder('i')
            ->where('i.owner = :user')
            ->setParameter('user', $this->getUser())
            ->getQuery();
        
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );
        
        return $this->render('my_inventory/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }
    
    #[Route('/inventory/shared', name: 'my_inventory_shared', methods: ['GET'])]
    public function shared(
        InventoryRepository $inventoryRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $query = $inventoryRepository->createQueryBuilder('i')
            ->innerJoin('i.writers', 'w')
            ->where('w = :user')
            ->setParameter('user', $this->getUser())
            ->getQuery();
        
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );
        
        return $this->render('my_inventory/shared.html.twig', [
            'pagination' => $pagination,
        ]);
    }
    
    #[Route('/inventory/editable', name: 'my_inventory_editable', methods: ['GET'])]
    public function editable(
        InventoryRepository $inventoryRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $inventories = $inventoryRepository->findEditableByUser($this->getUser());
        
        $pagination = $paginator->paginate(
            $inventories,
            $request->query->getInt('page', 1),
            10
        );
        
        return $this->render('my_inventory/editable.html.twig', [
            'pagination' => $pagination,
        ]);
    }
}