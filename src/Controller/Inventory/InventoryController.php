<?php
declare(strict_types=1);

namespace App\Controller\Inventory;

use App\Entity\Inventory;
use App\Form\InventoryType;
use App\Repository\InventoryRepository;
use App\Service\AccessChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/inventory')]
final class InventoryController extends AbstractController
{
    public function __construct(
        private readonly AccessChecker $accessChecker
    ) {}
    
    #[Route('', name: 'inventory_index', methods: ['GET'])]
    public function index(InventoryRepository $inventoryRepository): Response
    {
        $inventories = $inventoryRepository->findAllWithRelations();
        
        return $this->render('inventory/index.html.twig', [
            'inventories' => $inventories,
        ]);
    }
    
    #[Route('/new', name: 'inventory_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $inventory = new Inventory();
        
        $inventory->setOwner($this->getUser());

        if (!$this->accessChecker->canCreate($inventory)) {
            $this->addFlash('error', 'You cannot create inventory.');
            return $this->redirectToRoute('inventory_index');
        }
        
        $form = $this->createForm(InventoryType::class, $inventory);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($inventory);
            $entityManager->flush();
            
            $this->addFlash('success', 'Inventory created successfully.');
            return $this->redirectToRoute('inventory_show', ['id' => $inventory->getId()]);
        }
        
        return $this->render('inventory/new.html.twig', [
            'form' => $form->createView(),
            'inventory' => $inventory,
        ]);
    }
    
    #[Route('/{id}', name: 'inventory_show', methods: ['GET'])]
    public function show(Inventory $inventory): Response
    {
        return $this->render('inventory/show.html.twig', [
            'inventory' => $inventory,
        ]);
    }
    
    #[Route('/{id}/edit', name: 'inventory_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Inventory $inventory, EntityManagerInterface $entityManager): Response
    {
        if (!$this->accessChecker->canEdit($inventory)) {
            $this->addFlash('error', 'You cannot edit this inventory.');
            return $this->redirectToRoute('inventory_show', ['id' => $inventory->getId()]);
        }
        
        $form = $this->createForm(InventoryType::class, $inventory);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Inventory updated successfully.');
            return $this->redirectToRoute('inventory_show', ['id' => $inventory->getId()]);
        }
        
        return $this->render('inventory/edit.html.twig', [
            'form' => $form->createView(),
            'inventory' => $inventory,
        ]);
    }
    
    #[Route('/{id}', name: 'inventory_delete', methods: ['POST'])]
    public function delete(Request $request, Inventory $inventory, EntityManagerInterface $entityManager): Response
    {
        if (!$this->accessChecker->canDelete($inventory)) {
            throw $this->createAccessDeniedException('You cannot delete this inventory.');
        }
        
        if ($this->isCsrfTokenValid('delete'.$inventory->getId(), $request->request->get('_token'))) {
            $entityManager->remove($inventory);
            $entityManager->flush();
            $this->addFlash('success', 'Inventory deleted successfully.');
        }
        
        return $this->redirectToRoute('inventory_index');
    }
}
