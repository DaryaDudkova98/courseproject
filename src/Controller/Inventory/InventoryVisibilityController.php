<?php

namespace App\Controller\Inventory;

use App\Entity\Inventory;
use App\Service\AccessChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/inventory/{id}/visibility')]
final class InventoryVisibilityController extends AbstractController
{
    public function __construct(
        private readonly AccessChecker $accessChecker,
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('/toggle', name: 'inventory_toggle_public', methods: ['POST'])]
    public function togglePublic(Request $request, Inventory $inventory): Response
    {
        if (!$this->accessChecker->canTogglePublic($inventory)) {
            $this->addFlash('error', 'You cannot change visibility of this inventory.');
            return $this->redirectToRoute('inventory_show', ['id' => $inventory->getId()]);
        }

        if (!$this->isCsrfTokenValid('toggle' . $inventory->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $inventory->setPublic(!$inventory->isPublic());
        $this->entityManager->flush();

        $message = $inventory->isPublic()
            ? 'Inventory is now public.'
            : 'Inventory is now private.';
        $this->addFlash('success', $message);

        return $this->redirectToRoute('inventory_show', ['id' => $inventory->getId()]);
    }
}