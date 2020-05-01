<?php

namespace App\Controller;
use App\Entity\Forum;
use App\Entity\User;
use App\Form\PostForumType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


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

        $repository = $this->getDoctrine()->getRepository(Forum::class);
        $forum = $repository->findAll();


        return $this->render('forum/index.html.twig', [
            'forum' => $forum,
            'user' => $this->getUser()
        ]);
    }

    /**
     * @Route("/post/{forum}", name="post_unique")
     * @param Forum $forum
     *
     * @return Response
     */
    public function postunique(EntityManagerInterface $entityManager, Forum $forum)
    {
        $repository = $this->getDoctrine()->getRepository(Forum::class);

        $forumunique = $repository->findOneBy(
            ['id' => $forum],
        );
        return $this->render('forum/unique.html.twig', [
            'forum' => $forumunique
        ]);
    }

    
    /**
    * @Route("/creerpost", name="post_topic")
    */
    public function post(Request $request): Response
    {    
  
        $forum = new Forum();
        $form = $this->createForm(PostForumType::class, $forum);
        $form->handleRequest($request);
        $date = new \DateTime('@'.strtotime('now'));


        if ($form->isSubmitted() && $form->isValid()) {

            $forum->setAuteur($this->getUser());
            $forum->setDate($date);

         
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($forum);
            $entityManager->flush();

            // do anything else you need here, like send an email
        }
        return $this->render('forum/post.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
