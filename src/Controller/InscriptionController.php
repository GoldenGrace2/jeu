<?php
namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Routing\Annotation\Route;

class InscriptionController extends AbstractController
{
    /**
    * @Route("/", name="inscription")
    */
        
    public function new(Request $request)
    {
      
        // creates a task and gives it some dummy data for this example
        $user = new User();
        $user->setUsername('');

        $form = $this->createFormBuilder($user)
        ->add('username', TextType::class, ['label' => 'Pseudo'])
        ->add('password', TextType::class, ['label' => 'Mot de passe'])
        ->add('email', TextType::class, ['label' => 'Email'])
        ->add('nom', TextType::class, ['label' => 'Nom'])
        ->add('prenom', TextType::class, ['label' => 'Prénom'])
        ->add('tel', TextType::class, ['label' => 'Téléphone'])

        ->add('save', SubmitType::class, ['label' => 'Confirmer'])

            ->getForm();


        return $this->render('inscription/index.html.twig', array(
            'form' => $form->createView(),
            'controller_name' => 'InscriptionController',
        ));
    }
}