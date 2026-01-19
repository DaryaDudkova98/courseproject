<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SalesforceFormController extends AbstractController
{
    #[Route('/salesforce/form', name: 'salesforce_form')]
    public function form(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        return $this->render('profile/salesforce.html.twig');
    }
}