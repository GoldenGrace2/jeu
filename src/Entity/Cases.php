<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CasesRepository")
 */
class Cases
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $image;

    /**
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $effet;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $complement;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Journee;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

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

    public function getEffet(): ?string
    {
        return $this->effet;
    }

    public function setEffet(string $effet): self
    {
        $this->effet = $effet;

        return $this;
    }

    public function getJson()
    {
        //on pourrait le faire avec un sérializer... mais pas l'objet de ce module
        $t['position'] = $this->getPosition();
        $t['effet'] = $this->getEffet();
        $t['image'] = $this->getImage();
        $t['id'] = $this->getId();


        return $t;
    }

    public function getComplement(): ?string
    {
        return $this->complement;
    }

    public function setComplement(string $complement): self
    {
        $this->complement = $complement;

        return $this;
    }

    public function getComplementArray()
    {
        return json_decode($this->complement, true);
    }

    public function getJournee(): ?string
    {
        return $this->Journee;
    }

    public function setJournee(string $Journee): self
    {
        $this->Journee = $Journee;

        return $this;
    }


}
