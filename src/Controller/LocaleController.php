<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LocaleController extends AbstractController
{
    #[Route('/change-locale/{locale}', name: 'app_change_locale')]
    public function changeLocale(Request $request, string $locale): Response
    {
        $supportedLocales = ['en', 'es'];
        
        if (!in_array($locale, $supportedLocales)) {
            $locale = 'en';
        }
        
        $request->getSession()->set('_locale', $locale);
        
        $redirect = $request->query->get('_redirect');
        if ($redirect) {
            return $this->redirect($redirect);
        }
        
        if (strpos($request->headers->get('referer', ''), '/admin') !== false) {
            return $this->redirectToRoute('admin');
        }
        
        $referer = $request->headers->get('referer');
        
        if ($referer) {
            return $this->redirect($referer);
        }
        
        return $this->redirectToRoute('homepage');
    }
}