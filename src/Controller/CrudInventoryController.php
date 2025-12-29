<?php

namespace App\Controller;

use App\Entity\Inventory;
use App\Form\InventoryType;
use App\Repository\InventoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/crud/inventory')]
final class CrudInventoryController extends AbstractController
{
    #[Route(name: 'app_crud_inventory_index', methods: ['GET'])]
    public function index(InventoryRepository $inventoryRepository): Response
    {
        return $this->render('crud_toolbar/index.html.twig', [
            'inventories' => $inventoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_crud_inventory_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $inventory = new Inventory();

        $this->denyAccessUnlessGranted('NEW', $inventory);

        $form = $this->createForm(InventoryType::class, $inventory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($inventory);
            $entityManager->flush();

            return $this->redirectToRoute('app_crud_inventory_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('crud_toolbar/new.html.twig', [
            'inventory' => $inventory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_crud_inventory_show', methods: ['GET'])]
    public function show(Inventory $inventory): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $inventory);

        return $this->render('crud_toolbar/show.html.twig', [
            'inventory' => $inventory,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_crud_inventory_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Inventory $inventory, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $inventory);

        $form = $this->createForm(InventoryType::class, $inventory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_crud_inventory_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('crud_toolbar/edit.html.twig', [
            'inventory' => $inventory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_crud_inventory_delete', methods: ['POST'])]
    public function delete(Request $request, Inventory $inventory, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('DELETE', $inventory);

        if ($this->isCsrfTokenValid('delete'.$inventory->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($inventory);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_crud_inventory_index', [], Response::HTTP_SEE_OTHER);
    }
}
