<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MentionsController extends AbstractController
{
    /**
     * @Route("/mentions", name="app_mention")
     */
    public function mentions()
    {
        return $this->render('mentions/index.html.twig', [
            'controller_name' => 'MentionsController',
        ]);
    }

    /**
     * @Route("/cgu", name="app_cgu")
     */
    public function cgu()
    {
        return $this->render('mentions/cgu.html.twig', [
            'controller_name' => 'MentionsController',
        ]);
    }
}
