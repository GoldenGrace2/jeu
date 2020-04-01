<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CartesController extends AbstractController
{
    /**
     * @Route("/cartes", name="cartes")
     */
    public function index()
    {
        return $this->render('cartes/index.html.twig', [
            'controller_name' => 'CartesController',
        ]);
    }
}
