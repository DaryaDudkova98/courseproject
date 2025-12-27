<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class InventoryController extends AbstractController
{
    #[Route('/inventory', name: 'inventory')]
    public function index(): Response
    {
        $inventory = [['id' => 1, 'name' => 'Конфеты', 'quantity' => 10],
        ['id' => 2, 'name' => 'Чай', 'quantity' => 5],
        ['id' => 3, 'name' => 'Кофе', 'quantity' => 7],];

        return $this->render('inventory/index.html.twig', [
            'controller_name' => 'InventoryController',
            'inventory' => $inventory,
        ]);
    }
}
