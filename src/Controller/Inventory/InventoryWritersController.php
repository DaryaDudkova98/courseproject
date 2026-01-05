<?php

namespace App\Controller\Inventory;

use App\Entity\Inventory;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AccessChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/inventory/{id}/writers')]
final class InventoryWritersController extends AbstractController
{
    public function __construct(
        private readonly AccessChecker $accessChecker,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository
    ) {}
    
    #[Route('/manage', name: 'inventory_manage_writers', methods: ['GET', 'POST'])]
    public function manageWriters(Request $request, Inventory $inventory): Response
    {
        if (!$this->accessChecker->canManageWriters($inventory)) {
            $this->addFlash('error', 'You cannot manage writers for this inventory.');
            return $this->redirectToRoute('inventory_show', ['id' => $inventory->getId()]);
        }
        
        if ($request->isMethod('POST')) {
            $userIds = $request->request->all('writers');
            $this->updateWriters($inventory, $userIds);
            
            $this->addFlash('success', 'Writers updated successfully.');
            return $this->redirectToRoute('inventory_show', ['id' => $inventory->getId()]);
        }
        
        $users = $this->getAvailableUsers($inventory);
        
        return $this->render('inventory/writers/manage.html.twig', [
            'inventory' => $inventory,
            'users' => $users,
        ]);
    }
    
    private function updateWriters(Inventory $inventory, array $userIds): void
    {
        $inventory->getWriters()->clear();
        
        foreach ($userIds as $userId) {
            $writer = $this->userRepository->find($userId);
            if ($writer && $writer !== $this->getUser() && $writer !== $inventory->getOwner()) {
                $inventory->addWriter($writer);
            }
        }
        
        $this->entityManager->flush();
    }
    
    private function getAvailableUsers(Inventory $inventory): array
    {
        return $this->userRepository->createQueryBuilder('u')
            ->where('u != :owner')
            ->andWhere('u.status = :status')
            ->setParameter('owner', $inventory->getOwner())
            ->setParameter('status', User::STATUS_ACTIVE)
            ->getQuery()
            ->getResult();
    }
}