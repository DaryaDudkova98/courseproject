<?php

namespace App\Controller\Admin;

use Symfony\Component\Security\Core\User\UserInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/')]
class DashboardController extends AbstractDashboardController
{
    #[Route('/', name: 'admin')]
    public function index(): Response
    {
        return $this->render('dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('My Admin Panel');
    }

    public function configureTemplates(): array
    {
        return [
            'layout' => 'bundles/EasyAdminBundle/layout.html.twig',
        ];
    }


    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Home', 'fa fa-home');
        yield MenuItem::linkToRoute('Profile', 'fa fa-tags', 'user_profile');
        yield MenuItem::linkToRoute('Inventory', 'fa fa-tags', 'inventory');

        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::linkToRoute('Admin Panel', 'fa fa-tags', 'admin_panel');
        }
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)
            ->addMenuItems([
                MenuItem::linkToLogout('Exit', 'fa fa-sign-out'),
            ]);
    }
}
