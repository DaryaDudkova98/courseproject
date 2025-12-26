<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminToolbarController extends AbstractController
{
    #[Route('/admin/action', name: 'admin_action_toolbar', methods: ['POST'])]
    public function toolbar(Request $request, EntityManagerInterface $em): Response
    {
        $action = $request->request->get('action');
        $selectedIds = $request->request->all('selected');

        if (!$selectedIds || !$action) {
            $this->addFlash('warning', 'No users selected or no action specified.');
            return $this->redirectToRoute('admin_users');
        }

        $selectedIds = array_map('intval', $selectedIds);

        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        $isSelfAction = $currentUser instanceof User && in_array($currentUser->getId(), $selectedIds);

        if ($isSelfAction && in_array($action, ['remove', 'delete', 'block'])) {

            $this->container->get('security.token_storage')->setToken(null);
            $request->getSession()->invalidate();

            if ($action === 'block') {
                $this->addFlash('error', 'You have been blocked.');
            } elseif ($action === 'delete') {
                $this->addFlash('error', 'Your account has been deleted.');
            } elseif ($action === 'remove') {
                $this->addFlash('error', 'Your account has been removed.');
            }

            if ($action === 'remove') {
                $userToRemove = $em->getRepository(User::class)->find($currentUser->getId());
                if ($userToRemove) {
                    $em->remove($userToRemove);
                    $em->flush();
                }
            }

            return $this->redirectToRoute('app_login');
        }

        $users = $em->getRepository(User::class)->findBy(['id' => $selectedIds]);

        foreach ($users as $user) {
            switch ($action) {
                case 'block':
                    $user->setStatus('blocked');
                    break;

                case 'unblock':
                    $user->setStatus('active');
                    break;

                case 'delete':
                    $user->setStatus('deleted');
                    break;

                case 'remove':
                    $em->remove($user);
                    break;
            }
        }

        $em->flush();

        $this->addFlash('success', 'Action applied to selected users.');
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/admin/users', name: 'admin_users')]
    public function users(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findAll();

        return $this->render('admin_dashboard.html.twig', [
            'users' => $users,
        ]);
    }
}