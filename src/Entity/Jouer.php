<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JouerRepository")
 */
class Jouer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Partie", inversedBy="jouers")
     */
    private $partie;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="jouers")
     */
    private $joueur;

    /**
     * @ORM\Column(type="float")
     */
    private $argent = 120;

    /**
     * @ORM\Column(type="integer")
     */
    private $classement;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $cartes = '';

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $pion ='pion';

    /**
     * @ORM\Column(type="integer")
     */
    private $position = 1;

    /**
     * @ORM\Column(type="integer")
     */
    private $tour = 1;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $JPO;


    public function __toString()
    {
        //cette méthode permet de retourner du texte dans easyadmin, pour les relations.
        return '-'.$this->getId(); //je retourne l'id, il n'y a rien d'autre, a voir.
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPartie(): ?Partie
    {
        return $this->partie;
    }

    public function setPartie(?Partie $partie): self
    {
        $this->partie = $partie;

        return $this;
    }

    public function getJoueur(): ?User
    {
        return $this->joueur;
    }

    public function setJoueur(?User $joueur): self
    {
        $this->joueur = $joueur;

        return $this;
    }

    public function getArgent(): ?float
    {
        return $this->argent;
    }

    public function setArgent(float $argent): self
    {
        $this->argent = $argent;

        return $this;
    }

    public function getClassement(): ?int
    {
        return $this->classement;
    }

    public function setClassement(int $classement): self
    {
        $this->classement = $classement;

        return $this;
    }

    public function getPion(): ?string
    {
        return $this->pion;
    }

    public function setPion(string $pion): self
    {
        $this->pion = $pion;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getTour(): ?int
    {
        return $this->tour;
    }

    public function setTour(int $tour): self
    {
        $this->tour = $tour;

        return $this;
    }

    public function getJPO(): ?int
    {
        return $this->JPO;
    }

    public function setJPO(?int $JPO): self
    {
        $this->JPO = $JPO;

        return $this;
    }

        public function getCartes(): ?array
    {
        return json_decode($this->cartes, true);
    }

    public function setCartes(?array $cartes): self
    {
        $this->cartes = json_encode($cartes);

        return $this;
    }


}
