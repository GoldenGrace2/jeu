<?php

namespace App\Controller;

use App\Entity\Jouer;
use App\Entity\User;
use App\Entity\Partie;
use App\Entity\Chat;
use App\Entity\Logs;


use App\Repository\CarteRepository;
use App\Repository\PartieRepository;
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
            shuffle($tableauDeCartes['I']);
            shuffle($tableauDeCartes['A']);
            shuffle($tableauDeCartes['M']);

            $partie = new Partie();
            $partie->setPioche($tableauDeCartes);
            $partie->setEtatPartie('EC');
            $partie->setQuiJoue($this->getUser()->getId());
            $em = $this->getDoctrine()->getManager();
            $em->persist($partie);

            $logs = new Logs();
            $creationlogs = "Et c'est ".$this->getUser()." qui commence la partie! Bonne chance à tous.";
            $logs->setText($creationlogs);
            $logs->setPartie($partie);
            $em = $this->getDoctrine()->getManager();
            $em->persist($logs);
            
        
            for ($i = 1; $i <= 6; $i++) {
                //maximum 6 joueurs
                $j = $request->request->get('joueur' . $i);
                if ($j !== '') {
                    $joueur = $userRepository->find($j);
                    $jouer = new Jouer();
                    $jouer->setCartes(["I" => [], "A" => [], "M" => []]);
                    $jouer->setPartie($partie);
                    $jouer->setPion($request->request->get('pion' . $i)); //a gérer peut être autrement si provient d'une table
                    $jouer->setClassement($i);
                    $jouer->setJoueur($joueur);
                    $jouer->setPosition(0);
                    $jouer->setJPO(-5);

                    $em->persist($jouer);
                }
            }
            $em->flush();
            return $this->redirectToRoute('affiche_code_partie', ['partie' => $partie->getId()]);
        }

        $userverify = $this->getUser(); //Vérifier si l'utilisateur à confirmé son mail
        if ( $userverify->getConfirmation() == 0){ 

            return $this->render('partie/creerpartie.html.twig',
                [
                    'joueurs' => $userRepository->findAll()
                ]);

        }
        else {
            return $this->redirectToRoute('app_erreur');

        }

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
     * @param JouerRepository $jouerRepository
     * @param CasesRepository $casesRepository
     * @param PartieRepository $partieRepository
     * @param Partie          $partie
     * @param Jouer           $jouer
     *
     * @return Response
     * @throws NonUniqueResultException
     */
    public function affichePartie(
        CarteRepository $carteRepository,
        PartieRepository $partieRepository,
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

        $logRep = $this->getDoctrine()->getRepository(Logs::class);
        $chatRep = $this->getDoctrine()->getRepository(Chat::class);

        $logs = $logRep->findBy(
            ['partie' => $partie]
        );
        $chat = $chatRep->findBy(
            ['partie' => $partie]
        );

        return $this->render('partie/affichePartie.html.twig',
            [
                'cases'     => $casesRepository->findBy([], ['position' => 'ASC']),
                'jouer'     => $jouer,
                'user'      => $this->getUser(),
                'cartes'    => $carteRepository->findByArray(),
                'logs'      => $logs,
                'chat'      => $chat,
                'partie'    => $partie,
                'positions' => $positions,
                'mesdatas'  => $jouerRepository->findByJoueurAndPartie($partie, $this->getUser())
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
        Partie $partie    ) {
        
        $jouer = $jouerRepository->findByJoueurAndPartie($partie, $this->getUser());
        $cartes = $carteRepository->findByArray();
        $nbCases = count($casesRepository->findAll());

        if ($jouer !== null) {

            

            $de = rand(1,12);
            $position = $jouer->getPosition() + $de;

            //$de = 1; //rand(0, 6);
            //Fabien, tu pourrais tester lors de ton lancé de dès 
            //en fonction de ta case départ et de ta case d'arrivée, si la case en question est comprise entre ceux deux valeurs, donc tu ne t'es pas arreté
       
            $finTour = false;
            $data = '';
            if ($position >= $nbCases) {
                $position = $nbCases; //fin du tour
                $finTour = true;
            }
           $jouer->setPosition($position);

           $resultde = ' à lancé(e) le dé et fait ';
           $positionfin = ' '.$this->getUser().' est désormais à la position'.$jouer->getPosition().' !';
           $logs = new Logs();
           $logs->setText($this->getUser().$resultde.$de.$positionfin);
           $logs->setPartie($partie);
           $em = $this->getDoctrine()->getManager();
           $em->persist($logs);
            
           if ($jouer->getPosition() > 31 || $jouer->getPosition() == 31) {
            $jouer->setArgent($jouer->getArgent() +120);//jour de paye
            $this->getUser()->setScore($this->getUser()->getScore() + 120); 
            $jouer->setJPO(-5); 
            $jouer->setPosition(0); 
            $jouer->setTour($jouer->getTour() + 1); 


            $mesCartes = $jouer->getCartes();
            while ($carte = array_pop($mesCartes['M'])) {
                if ($cartes[$carte]->getTitre() == 'jpo') { $jouer->setPosition(3);}
                else {
                $jouer->setArgent($jouer->getArgent() + $cartes[$carte]->getValeur());//paiement des factures 
                }

            }
            $jouer->setCartes($mesCartes);//on remets pour vider le tableau de carte courrier

            $jourdepaye = 'Ohohoh! Le point projet à rapporté 120 points pour '.$this->getUser().'. Il est temps pour ce joueur de retourner à la case départ!';
            $logs = new Logs();
            $logs->setText($jourdepaye);
            $logs->setPartie($partie);
            $em = $this->getDoctrine()->getManager();
            $em->persist($logs);

            if ($jouer->getTour() == 5) {
                $partie->setEtatPartie('T');
                $fin = 'C\'est terminée! '.$this->getUser().' à atteint le cinquième tour.';
                $logs = new Logs();
                $logs->setText($jourdepaye.$this->getUser());
                $logs->setPartie($partie);
                $em = $this->getDoctrine()->getManager();
                $em->persist($logs);
                }


            } //Fin du script fin de tour //
       
        
            else {
            $case = $casesRepository->getDataCase($position); //on récupère les infos de la case
            //Il faut traiter l'action de la case et mettre à jour JOUER en fonction.
            //il faudrait s'assurer que la case n'est pas null, mais on va considérer que tout est OK ici
         
            switch ($case->getEffet()) {
                //le champs effet doit permettre de savoir quoi faire sur la case
                //j'ai ajouté un champs "complement" pour avoir des infos sur la valeur de l'action de la case: exemple 2 cartes courriers
                case 'Mail':
                    //il faut piocher des cartes mails et les mettre dans la main du joueur
                    $tabCartes = $partie->getPioche(); //je récupère les cartes courriers de la pioche
                    $mesCartes = $jouer->getCartes(); //je récupère mes cartes
                    $data = [];
                    //todo: il faudrait tester si j'ai assez de carte...
                    for ($i = 0; $i < $case->getComplement(); $i++) {
                        //on dépile le nombre de carte de la pioche, et on empile dans joueur.
                        //pour l'affichage,
                        $carte = array_pop($tabCartes['M']);
                        $mesCartes['M'][] = $carte;
                        $data[] = $carte; //je sauvegarde aussi dans un tableau intermédiaire pour afficher en JS plus facilement
                    }
                    //mise à jour de partie pour la pioche, et de jouer pour mes cartes
                    $partie->setPioche($tabCartes);
                    $jouer->setCartes($mesCartes);

                    $logmail = $this->getUser().' vient de piocher '.$case->getComplement(). ' cartes mail(s).';
                    $logs = new Logs();
                    $logs->setText($logmail);
                    $logs->setPartie($partie);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($logs);
                    
                    break;
                case 'Rapport':
                    //cette case preleve directement le montant au joueur
                    $de_alea= rand(1,6);
                    if ($de_alea == 6) { $lograpport = 'Il est l\'heure de rendre le de rapport de conception '.$this->getUser().' et tu as fait 6 avec ton dé, quel boss! '.$this->getUser().' gagne donc 10 points pour son rapport de conception parfait.';
                        $logs = new Logs();
                        $logs->setText($lograpport);
                        $logs->setPartie($partie);
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($logs);}
                    else { 
                        $lograpport = 'C\'est l\'heure du rapport de conception et... Pas de chance '.$this->getUser().', tu as fait '.$de_alea.' avec ton dé et pas 6. Peut-être une autre fois!';
                        $logs = new Logs();
                        $logs->setText($lograpport);
                        $logs->setPartie($partie);
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($logs);

                    }
                    //je sauvegarde rien de particulier pour le JS, toutes les infos sont dans la case
                    break;
                case 'Imprevu':
                                   //il faut piocher des cartes mails et les mettre dans la main du joueur
                                   $tabCartes = $partie->getPioche(); //je récupère les cartes courriers de la pioche
                                   $mesCartes = $jouer->getCartes(); //je récupère mes cartes
                                   $data = [];
                                   //todo: il faudrait tester si j'ai assez de carte...
                                   for ($i = 0; $i < 1; $i++) {
                                       //on dépile le nombre de carte de la pioche, et on empile dans joueur.
                                       //pour l'affichage,
                                       $carte = array_pop($tabCartes['I']);
                                       $mesCartes['I'][] = $carte;
                                       $data[] = $carte; //je sauvegarde aussi dans un tableau intermédiaire pour afficher en JS plus facilement
                                   }
                                   //mise à jour de partie pour la pioche, et de jouer pour mes cartes
                                   $partie->setPioche($tabCartes);
                                   $jouer->setCartes($mesCartes);

                                    $carte = array_pop($mesCartes['I']);

                                    if (empty($carte)) {
                                        $logimprevu = $this->getUser().' vient de piocher rien du tout car la pioche imprévu est vide!';
                                        $logs = new Logs();
                                        $logs->setText($logimprevu);
                                        $logs->setPartie($partie);
                                        $em = $this->getDoctrine()->getManager();
                                        $em->persist($logs);
                                   }

                                   else {

                                    $logimprevu = $this->getUser().' vient de piocher une carte imprévu! Son score est passée de '.strval($jouer->getArgent());
                                    $cagnotte = '';
                                    $jouer->setArgent($jouer->getArgent() + $cartes[$carte]->getValeur());//paiement des factures
                                    if ($cartes[$carte]->getTitre() == 'cagnotte') {  $jouer->setArgent($jouer->getArgent() + $partie->getGagnotte()); $cagnotte = ' JACKPOT POUR '.$this->getUser().' qui remporte la cagnotte!'; }
                                    $partie->setCagnotte($partie->getGagnotte() + $cartes[$carte]->getCout());
                                    $logimprevufin = ' à '.strval($jouer->getArgent()).'.';
                                    $logs = new Logs();
                                    $logs->setText($logimprevu.$logimprevufin.$cagnotte);
                                    $logs->setPartie($partie);
                                    $em = $this->getDoctrine()->getManager();
                                    $em->persist($logs);
                                }
                    break;
                case 'Caution':
                        $tabCartes = $partie->getPioche(); //je récupère les cartes courriers de la pioche
                        $mesCartes = $jouer->getCartes(); //je récupère mes cartes
                        $data = [];
                        //todo: il faudrait tester si j'ai assez de carte...
                        for ($i = 0; $i < 1; $i++) {
                            //on dépile le nombre de carte de la pioche, et on empile dans joueur.
                            //pour l'affichage,
                            $carte = array_pop($tabCartes['A']);
                            $mesCartes['A'][] = $carte;
                            $data[] = $carte; //je sauvegarde aussi dans un tableau intermédiaire pour afficher en JS plus facilement
                            $jouer->setArgent($jouer->getArgent() + $cartes[$carte]->getValeur() + $cartes[$carte]->getValeur());
                        }
                        //mise à jour de partie pour la pioche, et de jouer pour mes cartes
                        $partie->setPioche($tabCartes);
                        $jouer->setCartes($mesCartes);

                        $logmail = $this->getUser().' vient de piocher une carte à prendre ou à laisser.';
                        $logs = new Logs();
                        $logs->setText($logmail);
                        $logs->setPartie($partie);
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($logs);
                        break;
                case 'Emprunt':
                        $mesCartes = $jouer->getCartes();
                        while ($carte = array_pop($mesCartes['A'])) {
                            $jouer->setArgent($jouer->getArgent() + $cartes[$carte]->getValeur() + $cartes[$carte]->getValeur());//paiement des factures
                        }
                        $jouer->setCartes($mesCartes);//on remets pour vider le tableau de carte à prendre ou à laisser

                        $logsemprunt = $this->getUser().' vient de tomber sur la case emprunt, peut-être que '.$this->getUser().' à des cartes à prendre ou à laisser à vendre?!';
                                    $logs = new Logs();
                                    $logs->setText($logsemprunt);
                                    $logs->setPartie($partie);
                                    $em = $this->getDoctrine()->getManager();
                                    $em->persist($logs);

                        break;
                case 'Embrouille':
                    $logs = new Logs();
                    $logs->setText('Ah bah bravo,'.$this->getUser().' vient de s\'embrouiller avec son groupe! Il perd donc son tour...');
                    $logs->setPartie($partie);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($logs);
                     break;
                case 'Presentation':
                        $logs = new Logs();
                        $logs->setText('Il est temps de présenter le meilleur diapo de tout les temps, '.$this->getUser().'.');
                        $logs->setPartie($partie);
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($logs);
                         break;
                case 'JPO':
                    //Pour ajouter ou retirer des points avec une case
                    if ( $jouer->getJPO() === -5) {
                        $jouer->setJPO(0);
                        $logs = new Logs();
                        $logs->setText('C\'est beau ça... '.$this->getUser().' est venu à la journée porte ouverte, ce joueur ne va donc ne pas subir de pénalité!');
                        $logs->setPartie($partie);
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($logs);
                    }
               
                    //$jouer->setArgent($jouer->getArgent() + $case->getComplement());
                     break;
                case 'Débat':
                    $logdebat = 'Il est l\'heure de débattre, '.$this->getUser().'!';
                        $logs = new Logs();
                        $logs->setText($logdebat);
                        $logs->setPartie($partie);
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($logs);
                    break;
                case 'Glandeur':
                    //Dimanche, rien a faire ;)
                    $logs = new Logs();
                    $logs->setText('Quelle belle journée pour glander, hein '.$this->getUser().'?');
                    $logs->setPartie($partie);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($logs);
                    break;
                case 'FinTour':
                    //jour de paye, payer les facture, verser le salaire, les interets eventuels
                 
                    break;
                case 'Depart':
                    break;
               

            }


    }  //Fin du else
            
            //Test pour la case JPO
            switch ($jouer->getJPO()) {
                case '-5':
                        switch ($jouer->getPosition()) {
                        case '4':
                        $jouer->setArgent($jouer->getArgent() - 5); 
                        $jouer->setJPO(0); 
                        $logs = new Logs();
                        $logs->setText('Mais quel cancre '.$this->getUser().'! Pour avoir sauté la case journée porte ouverte, tu perds 5 points. Non mais oh!');
                        $logs->setPartie($partie);
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($logs);
                        break;
                        case '5':
                            $jouer->setArgent($jouer->getArgent() - 5); 
                            $jouer->setJPO(0); 
                            $logs = new Logs();
                            $logs->setText('Mais quel cancre '.$this->getUser().'! Pour avoir sauté la case journée porte ouverte, tu perds 5 points. Non mais oh!');
                            $logs->setPartie($partie);
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($logs);
                        break;
                        case '6':
                            $jouer->setArgent($jouer->getArgent() - 5); 
                            $jouer->setJPO(0); 
                            $logs = new Logs();
                            $logs->setText('Mais quel cancre '.$this->getUser().'! Pour avoir sauté la case journée porte ouverte, tu perds 5 points. Non mais oh!');
                            $logs->setPartie($partie);
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($logs);
                        break;
                        case '7':
                            $jouer->setArgent($jouer->getArgent() - 5); 
                            $jouer->setJPO(0); 
                            $logs = new Logs();
                            $logs->setText('Mais quel cancre '.$this->getUser().'! Pour avoir sauté la case journée porte ouverte, tu perds 5 points. Non mais oh!');
                            $logs->setPartie($partie);
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($logs);
                        break;
                        case '8':
                            $jouer->setArgent($jouer->getArgent() - 5); 
                            $jouer->setJPO(0);
                        break;
                        case '9':
                            $jouer->setArgent($jouer->getArgent() - 5); 
                            $jouer->setJPO(0); 
                            $logs = new Logs();
                            $logs->setText('Mais quel cancre '.$this->getUser().'! Pour avoir sauté la case journée porte ouverte, tu perds 5 points. Non mais oh!');
                            $logs->setPartie($partie);
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($logs);
                        break;
                        case '10':
                            $jouer->setArgent($jouer->getArgent() - 5); 
                            $jouer->setJPO(0); 
                            $logs = new Logs();
                            $logs->setText('Mais quel cancre '.$this->getUser().'! Pour avoir sauté la case journée porte ouverte, tu perds 5 points. Non mais oh!');
                            $logs->setPartie($partie);
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($logs);
                        break;
                        case '11':
                            $jouer->setArgent($jouer->getArgent() - 5); 
                            $jouer->setJPO(0); 
                            $logs = new Logs();
                            $logs->setText('Mais quel cancre '.$this->getUser().'! Pour avoir sauté la case journée porte ouverte, tu perds 5 points. Non mais oh!');
                            $logs->setPartie($partie);
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($logs);
                        break;
                        case '12':
                            $jouer->setArgent($jouer->getArgent() - 5); 
                            $jouer->setJPO(0); 
                            $logs = new Logs();
                            $logs->setText('Mais quel cancre '.$this->getUser().'! Pour avoir sauté la case journée porte ouverte, tu perds 5 points. Non mais oh!');
                            $logs->setPartie($partie);
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($logs);
                        break;
                        case '13':
                            $jouer->setArgent($jouer->getArgent() - 5); 
                            $jouer->setJPO(0); 
                            $logs = new Logs();
                            $logs->setText('Mais quel cancre '.$this->getUser().'! Pour avoir sauté la case journée porte ouverte, tu perds 5 points. Non mais oh!');
                            $logs->setPartie($partie);
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($logs);
                        break;
                        case '14':
                            $jouer->setArgent($jouer->getArgent() - 5); 
                            $jouer->setJPO(0); 
                            $logs = new Logs();
                            $logs->setText('Mais quel cancre '.$this->getUser().'! Pour avoir sauté la case journée porte ouverte, tu perds 5 points. Non mais oh!');
                            $logs->setPartie($partie);
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($logs);
                        break;
                        case '15':
                            $jouer->setArgent($jouer->getArgent() - 5); 
                            $jouer->setJPO(0); 
                            $logs = new Logs();
                            $logs->setText('Mais quel cancre '.$this->getUser().'! Pour avoir sauté la case journée porte ouverte, tu perds 5 points. Non mais oh!');
                            $logs->setPartie($partie);
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($logs);
                        break;
                        }
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

        if ($partie->getQuiJoue() == $this->getUser()->getId()) {
            $logFinTour = $this->getUser().' vient de terminer son tour.';
                                        $logs = new Logs();
                                        $logs->setText($logFinTour);
                                        $logs->setPartie($partie);
                                        $em = $this->getDoctrine()->getManager();
                                        $em->persist($logs);
        }

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
     * @Route("/update-partie-chat/fin-tour/{partie}", name="chat_update")
     * @param Partie $partie
     *
     * @return Response
     */
    public function updateChat(EntityManagerInterface $entityManager, Partie $partie)
    {

    }

    /**
     * @Route("/affiche-plateau/{partie}", name="affiche_plateau")
     * @param JouerRepository $jouerRepository
     * @param CasesRepository $casesRepository
     * @param PartieRepository $partieRepository
     * @param Partie          $partie
     *
     * @return Response
     * @throws NonUniqueResultException
     */
    public function affichePlateau(
        CarteRepository $carteRepository,
        PartieRepository $partieRepository,
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
                'jouer'     => $jouer,
                'user'      => $this->getUser(),
                'cartes'    => $carteRepository->findByArray(),
                'partie'    => $partie,
                'positions' => $positions,
                'mesdatas'  => $jouerRepository->findByJoueurAndPartie($partie, $this->getUser())
            ]);
    }
}
