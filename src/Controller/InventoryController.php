<?php

namespace App\Controller;

use App\Repository\InventoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class InventoryController extends AbstractController
{
    #[Route('/inventory', name: 'inventory')]
    public function index(InventoryRepository $inventoryRepository): Response
    {
        $inventory = $inventoryRepository->findAll();

        return $this->render('inventory/index.html.twig', [
            'controller_name' => 'InventoryController',
            'inventory' => $inventory,
        ]);
    }
}

