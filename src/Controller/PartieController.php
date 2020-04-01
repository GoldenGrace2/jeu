<?php

namespace App\Controller;

use App\Entity\Jouer;
use App\Entity\User;
use App\Entity\Partie;
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

/**
 * @Route("/jouer")
 */
class PartieController extends AbstractController
{
    /**
     * @Route("/creer-partie", name="creer_partie")
     * @param Request         $request
     * @param UserRepository  $userRepository
     * @param CarteRepository $carteRepository
     *
     * @return RedirectResponse|Response
     */
    public function creerPartie(Request $request, UserRepository $userRepository, CarteRepository $carteRepository)
    {

        if ($request->isMethod('POST')) {


            $cartes = $carteRepository->findAll();
            foreach ($cartes as $carte) {
                $tableauDeCartes[$carte->getTypeDeCarte()][] = $carte->getId();
            }
            shuffle($tableauDeCartes['T']);
            shuffle($tableauDeCartes['C']);

            $partie = new Partie();
            $partie->setPioche($tableauDeCartes);
            $em = $this->getDoctrine()->getManager();
            $em->persist($partie);

            for ($i = 1; $i <= 6; $i++) {
                //maximum 6 joueurs
                $j = $request->request->get('joueur' . $i);
                if ($j !== '') {
                    $joueur = $userRepository->find($j);
                    $jouer = new Jouer();
                    $jouer->setCartes(["C" => [], "T" => []]);
                    $jouer->setPartie($partie);
                    $jouer->setPion($request->request->get('pion' . $i)); //a gérer peut être autrement si provient d'une table
                    $jouer->setClassement($i);
                    $jouer->setJoueur($joueur);
                    $em->persist($jouer);
                }
            }


            $em->flush();

            return $this->redirectToRoute('affiche_code_partie', ['partie' => $partie->getId()]);
        }

        return $this->render('partie/creerpartie.html.twig',
            [
                'joueurs' => $userRepository->findAll()
            ]);
    }

    /**
     * @Route("/affiche-code-partie/{partie}", name="affiche_code_partie")
     * @param Partie $partie
     *
     * @return Response
     */
    public function afficheCodePartie(Partie $partie)
    {
        return $this->render('partie/afficheCodePartie.html.twig',
            [
                'partie' => $partie
            ]);
    }

    /**
     * @Route("/affiche-partie/{partie}", name="affiche_partie")
     * @param Partie $partie
     *
     * @return Response
     */
    public function affichePartie(Partie $partie)
    {
        return $this->render('partie/affichePartie.html.twig',
            [
                'partie' => $partie
            ]);
    }

    /**
     * @Route("/update-partie/data/{partie}", name="update_game")
     * @param Partie $partie
     *
     * @return Response
     */
    public function updateGame(Partie $partie)
    {
        $jouers = $partie->getJouers();
        $monTour = false;
        $positions = [];
        foreach ($jouers as $jouer) {
            if ($partie->getQuiJoue() === $this->getUser()->getId()) {
                //quiJoue contient l'id du joueur en train de jouer.
                $monTour = true;
            }
            if ($jouer->getJoueur() !== null) {
                $positions[$jouer->getJoueur()->getId()]['username'] = $jouer->getJoueur()->getUsername();
                $positions[$jouer->getJoueur()->getId()]['position'] = $jouer->getPosition();
                $positions[$jouer->getJoueur()->getId()]['argent'] = $jouer->getArgent();
            }
        }

        $array = [
            'joueurEnCours'    => $partie->getQuiJoue(),
            'monTour'          => $monTour,
            'positionsJoueurs' => $positions
        ];

        return $this->json($array);
    }

    /**
     * @Route("/update-partie/lancé dé/{partie}", name="lance_de")
     * @param EntityManagerInterface $entityManager
     * @param JouerRepository        $jouerRepository
     * @param Partie                 $partie
     *
     * @return Response
     * @throws NonUniqueResultException
     */
    public function lanceDe(
        EntityManagerInterface $entityManager,
        CasesRepository $casesRepository,
        CarteRepository $carteRepository,
        JouerRepository $jouerRepository,
        Partie $partie
    ) {
        $jouer = $jouerRepository->findByJoueurAndPartie($partie, $this->getUser());
        $cartes = $carteRepository->findByArray();
        $nbCases = count($casesRepository->findAll());
        if ($jouer !== null) {
            $de = 1; //rand(0, 6);
            $position = $jouer->getPosition() + $de;
            $finTour = false;
            $data = '';
            if ($position >= $nbCases) {
                $position = $nbCases; //fin du tour
                $finTour = true;
            }

            $jouer->setPosition($position);
            $case = $casesRepository->getDataCase($position); //on récupère les infos de la case
            //Il faut traiter l'action de la case et mettre à jour JOUER en fonction.
            //il faudrait s'assurer que la case n'est pas null, mais on va considérer que tout est OK ici
            switch ($case->getEffet()) {
                //le champs effet doit permettre de savoir quoi faire sur la case
                //j'ai ajouté un champs "complement" pour avoir des infos sur la valeur de l'action de la case: exemple 2 cartes courriers
                case 'Courrier':
                    //il faut piocher des cartes courriers et les mettre dans la main du joueur
                    $tabCartes = $partie->getPioche(); //je récupère les cartes courriers de la pioche
                    $mesCartes = $jouer->getCartes(); //je récupère mes cartes
                    $data = [];
                    //todo: il faudrait tester si j'ai assez de carte...
                    for ($i = 0; $i < $case->getComplement(); $i++) {
                        //on dépile le nombre de carte de la pioche, et on empile dans joueur.
                        //pour l'affichage,
                        $carte = array_pop($tabCartes['C']);
                        $mesCartes['C'][] = $carte;
                        $data[] = $carte; //je sauvegarde aussi dans un tableau intermédiaire pour afficher en JS plus facilement
                    }
                    //mise à jour de partie pour la pioche, et de jouer pour mes cartes
                    $partie->setPioche($tabCartes);
                    $jouer->setCartes($mesCartes);
                    break;
                case 'Frais':
                    //cette case preleve directement le montant au joueur
                    $jouer->setArgent($jouer->getArgent() - $case->getComplementArray()['cout']);
                    //je sauvegarde rien de particulier pour le JS, toutes les infos sont dans la case
                    break;
                case 'Loterie':
                    //lancement de la loterie entre joueur
                    break;
                case 'Caution':
                    //on pioche une carte transaction qu'on propose au joueur
                    //il faut piocher des cartes courriers et les mettre dans la main du joueur
                    $tabCartes = $partie->getPioche(); //je récupère les cartes courriers de la pioche
                    $mesCartes = $jouer->getCartes(); //je récupère mes cartes
                    //todo: il faudrait tester si j'ai assez de carte...
                        //on dépile la carte transaction de la pioche, et on empile dans joueur.
                        $carte = array_pop($tabCartes['T']);
                        $mesCartes['T'][] = $carte;
                        $data = $carte; //je sauvegarde aussi dans un tableau intermédiaire pour afficher en JS plus facilement
                    //mise à jour de partie pour la pioche, et de jouer pour mes cartes
                    $partie->setPioche($tabCartes);
                    $jouer->setCartes($mesCartes);
                    break;
                case 'Dimanche':
                    //Dimanche, rien a faire ;)
                    break;
                case 'Vente':
                    //Vente possible des transactions
                    //récupère toutes les cartes transaction du jour pour qu'il puisse choisir celle(s) à vendre
                    $data = $jouer->getCartes()['T'];
                    break;
                case 'FinTour':
                    //jour de paye, payer les facture, verser le salaire, les interets eventuels
                    $jouer->setArgent($jouer->getArgent() +1500);//jour de paye
                    $mesCartes = $jouer->getCartes();
                    while ($carte = array_pop($mesCartes['C'])) {
                        $jouer->setArgent($jouer->getArgent() - $cartes[$carte]->getCout());//paiement des factures
                    }
                    $jouer->setCartes($mesCartes);//on remets pour vider le tableau de carte courrier
                    break;
            }

            $entityManager->persist($jouer);
            $entityManager->persist($partie);
            $entityManager->flush();//sauvegarde de l'entité partie

            $array = [
                'de'       => $de,
                'finTour'  => $finTour,
                'position' => $position,
                'case'     => $case,
                'data'     => $data //données de la case ou de l'action en cours
            ];

            return $this->json($array);
        }
    }

    /**
     * @Route("/update-partie/fin-tour/{partie}", name="fin_de_tour")
     * @param Partie $partie
     *
     * @return Response
     */
    public function finTour(EntityManagerInterface $entityManager, Partie $partie)
    {
        $jouers = $partie->getJouers();
        $positions = [];
        foreach ($jouers as $jouer) {
            if ($jouer->getJoueur()->getId() === $this->getUser()->getId()) {
                $monOrdre = $jouer->getClassement();
            }

            if ($jouer->getJoueur() !== null) {
                $positions[$jouer->getJoueur()->getId()]['username'] = $jouer->getJoueur()->getUsername();
                $positions[$jouer->getJoueur()->getId()]['position'] = $jouer->getPosition();
                $positions[$jouer->getJoueur()->getId()]['argent'] = $jouer->getArgent();
            }
            $ordre[$jouer->getClassement()] = $jouer->getJoueur()->getId();
        }

        if ($monOrdre >= count($ordre)) {
            $joueurSuivant = $ordre[1];
        } else {
            $joueurSuivant = $ordre[$monOrdre + 1];
        }

        $partie->setQuiJoue($joueurSuivant);
        $entityManager->persist($partie);
        $entityManager->flush();//sauvegarde de l'entité partie
        $array = [
            'joueurEnCours'    => $partie->getQuiJoue(),
            'monTour'          => false,
            'positionsJoueurs' => $positions
        ];

        return $this->json($array);
    }

    /**
     * @Route("/affiche-plateau/{partie}", name="affiche_plateau")
     * @param JouerRepository $jouerRepository
     * @param CasesRepository $casesRepository
     * @param Partie          $partie
     *
     * @return Response
     * @throws NonUniqueResultException
     */
    public function affichePlateau(
        CarteRepository $carteRepository,
        JouerRepository $jouerRepository,
        CasesRepository $casesRepository,
        Partie $partie
    ) {
        $jouers = $partie->getJouers();
        $positions = [];
        foreach ($jouers as $jouer) {
            if (!array_key_exists($jouer->getPosition(), $positions)) {
                $positions[$jouer->getPosition()] = [];
            }
            $positions[$jouer->getPosition()][] = $jouer;
        }

        return $this->render('partie/_affichePlateau.html.twig',
            [
                'cases'     => $casesRepository->findBy([], ['position' => 'ASC']),
                'cartes'    => $carteRepository->findByArray(),
                'partie'    => $partie,
                'positions' => $positions,
                'mesdatas'  => $jouerRepository->findByJoueurAndPartie($partie, $this->getUser())
            ]);
    }
}
