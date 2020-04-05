<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/inscription", name="app_inscription")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, \Swift_Mailer $mailer): Response
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);
        $user->setScore(0);
        $user->setConfirmation(rand(1,10000000));
        $user->setImg("base.jpg");
        $user->setIa(false);

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            $message = (new \Swift_Message('Confirmation d\'inscription'))
                ->setFrom('trashproject.gigame@gmail.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        // templates/emails/registration.html.twig
                        'emails/registration.html.twig',
                        [
                        'name' => $user->getUsername(),
                        'confirmation' => $user->getConfirmation()
                        ]
                    ),
                    'text/html'
                );

            $mailer->send($message);

            return $this->redirectToRoute('app_accueil');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/inscription/{confirmation}", name="app_confirmation")
     */
    public function confirmation($confirmation){
        $userconfirmation = $this->getUser();
        if ($confirmation == $userconfirmation->getConfirmation()){ 
            
            $userconfirmation->setConfirmation(0); 
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($userconfirmation);
            $entityManager->flush();
            
            return $this->render('inscription/confirmation.html.twig', [
                'user' => $this->getUser()
            ]);
        } 

        else {
            return $this->render('inscription/erreur.html.twig', [
            ]);
        }

    }

}
