<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HelloController extends AbstractController
{
    #[Route('/', name: 'hello_world')]
    public function hello(): Response
    {
        return $this->render('base.html.twig');
    }

    #[Route('/test', name: 'test')]
public function test(): Response
{
    return new Response('Symfony работает!');
}

}