<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

final class AdminDashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin_dashboard/index.html.twig', [
            'controller_name' => 'AdminDashboardController',
            'users' => $users,
        ]);
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user instanceof \App\Entity\User) {
            $this->addFlash('error', 'You must log in.');
            return $this->redirectToRoute('app_login');
        }

        $dbUser = $em->getRepository(\App\Entity\User::class)->find($user->getId());

        if (!$dbUser) {

            $this->container->get('security.token_storage')->setToken(null);
            $request->getSession()->invalidate();

            $this->addFlash('error', 'Your account has been removed.');
            return $this->redirectToRoute('app_login');
        }

        $statusValue = $dbUser->getStatus();

        if (!in_array($statusValue, ['active', 'unverified'])) {
            if ($statusValue === 'blocked') {
                $this->addFlash('error', 'Access denied. Your account has been blocked.');
            } elseif ($statusValue === 'deleted') {
                $this->addFlash('error', 'Access denied. Your account has been deleted.');
            }

            return $this->redirectToRoute('app_login');
        }

        $dbUser->setLastSeen(new \DateTime());
        $em->flush();

        return $this->render('dashboard.html.twig', [
            'user' => $dbUser,
        ]);
    }
}
