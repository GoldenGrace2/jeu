<?php

namespace App\Controller;

use App\Entity\Jouer;
use App\Entity\User;
use App\Entity\Partie;
use App\Entity\Chat;

use App\Repository\CarteRepository;
use App\Repository\CasesRepository;
use App\Repository\JouerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccueilController extends AbstractController
{
    /**
     * @Route("/", name="app_accueil")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();

        $partie = $em->getRepository(Partie::class);

        $totalpartie = $partie->createQueryBuilder('p')
        ->select('count(p.id)')
        ->getQuery()
        ->getSingleScalarResult();
        
        $partie_en_cours_total = $partie->createQueryBuilder('p')
        ->select('count(p.etatPartie)')
        ->where("p.etatPartie = 'EC'")
        ->getQuery()
        ->getSingleScalarResult();

        $partie_terminee = $partie->createQueryBuilder('p')
        ->select('count(p.etatPartie)')
        ->where("p.etatPartie = 'T'")
        ->getQuery()
        ->getSingleScalarResult();


        return $this->render('accueil/index.html.twig', [
            'controller_name' => 'AccueilController',
            'user' => $this->getUser(),
            'partie_total' => $totalpartie,
            'partie_total_en_cours' => $partie_en_cours_total,
            'partie_terminee' => $partie_terminee,
        ]);
    }
}
