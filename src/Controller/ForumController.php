<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
* @Route("/forum")
*/
class ForumController extends AbstractController
{

    /**
    * @Route("/", name="app_forum")
    */
    public function index()
    {
        return $this->render('forum/index.html.twig', [
            'controller_name' => 'ForumController',
        ]);
    }

       /**
     * @Route("/forum_post", name="app_forum_post")
     */
    public function post()
    {
        return $this->render('forum/post.html.twig', [
            'controller_name' => 'ForumController',
        ]);
    }
}
