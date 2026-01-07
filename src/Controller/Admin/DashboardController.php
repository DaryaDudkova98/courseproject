<?php

namespace App\Controller\Admin;

use App\Entity\Item;
use App\Entity\Inventory;
use Symfony\Component\Security\Core\User\UserInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
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
                ['%username%' => $this->getUser()->getUserIdentifier()]
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
            'app_profile'
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
            yield MenuItem::linkToRoute(
                $this->translator->trans('menu.admin_panel'),
                'fa fa-tools',
                'admin_panel'
            );
        }
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)
            ->addMenuItems([
                MenuItem::linkToLogout(
                    $this->translator->trans('user.logout'),
                    'fa fa-sign-out'
                ),
            ]);
    }

    ##[Route('/inventory', name: 'inventory')]
    #public function inventory(): Response
    #{
    #return $this->render('inventory/index.html.twig', [
    #'page_title' => $this->translator->trans('inventory.title'),
    #]);
    #}

    #[Route('/admin/panel', name: 'admin_panel')]
    public function adminPanel(): Response
    {
        return $this->render('admin_panel/index.html.twig', [
            'page_title' => $this->translator->trans('admin_panel.title'),
        ]);
    }

    #[Route('/profile', name: 'app_profile')]
    public function profile(): Response
    {
        return $this->render('profile/index.html.twig', [
            'page_title' => $this->translator->trans('profile.title'),
        ]);
    }
}
