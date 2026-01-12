<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private TranslatorInterface $translator
    ) {}

    #[Route('/', name: 'admin')]
    public function index(): Response
    {
        return $this->render('dashboard.html.twig', [
            'welcome_message' => $this->translator->trans(
                'dashboard.welcome',
                ['%username%' => $this->getUser()?->getUserIdentifier() ?? 'Guest']
            )
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle($this->translator->trans('dashboard.title'))
            ->setLocales(['en', 'es'])
            ->setTranslationDomain('messages');
    }

    public function configureTemplates(): array
    {
        return [
            'layout' => 'bundles/EasyAdminBundle/layout.html.twig',
        ];
    }

    public function configureMenuItems(): iterable
    {

        yield MenuItem::linkToDashboard(
            $this->translator->trans('menu.dashboard'),
            'fa fa-home'
        );

        yield MenuItem::linkToRoute(
            $this->translator->trans('menu.profile'),
            'fa fa-user',
            'admin_profile_index'
        );

        yield MenuItem::linkToRoute(
            $this->translator->trans('menu.inventory'),
            'fa fa-tags',
            'admin_inventory_index'
        );

        yield MenuItem::linkToRoute(
            $this->translator->trans('menu.item'),
            'fa fa-tags',
            'admin_item_index'
        );

        if ($this->isGranted('ROLE_ADMIN')) {
        yield MenuItem::linkToCrud(
            'Manage Users',
            'fas fa-users-cog',
            User::class
        );
    }
    }

    #[Route('/admin/panel', name: 'admin_panel')]
    public function adminPanel(): Response
    {
        return $this->render('admin_panel/index.html.twig', [
            'page_title' => $this->translator->trans('admin_panel.title'),
        ]);
    }
}
