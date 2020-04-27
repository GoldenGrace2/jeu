<?php

namespace App\Controller;

use App\Repository\PartieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class JoueurController
 * @package App\Controller
 * @Route("/joueur")
 */
class JoueurController extends AbstractController
{
    /**
     * @Route("/profil", name="joueur_profil")
     */
    public function profil(PartieRepository $partieRepository)
    {
        $parties = $partieRepository->findMyParties($this->getUser());

        return $this->render('joueur/profil.html.twig', [
            'joueur' => $this->getUser(),
            'parties' => $parties
        ]);
    }

    /**
     * @Route("/profil/modification/{}", name="app_joueur_modif")
     */
    public function modification()
    {
        $parties = $partieRepository->findMyParties($this->getUser());

        return $this->render('joueur/profil.html.twig', [
            'joueur' => $this->getUser(),
            'parties' => $parties
        ]);
    }
}
