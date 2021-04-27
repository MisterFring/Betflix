<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pseudo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $mail;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="date")
     */
    private $birthDate;

    /**
     * @ORM\Column(type="float")
     */
    private $credits;

    /**
     * @ORM\OneToMany(targetEntity=Bet::class, cascade={"persist", "remove"}, mappedBy="user")
     */
    private $bet;


    /**
     * User constructor.
     * @param $pseudo
     * @param $name
     * @param $password
     * @param $birthDate
     * @param $mail
     */
    public function __construct($pseudo, $name, $password, $birthDate, $mail)
    {
        $this->pseudo = $pseudo;
        $this->name = $name;
        $this->password = $password;
        $this->mail = $mail;
        $this->birthDate = $birthDate;
        $this->credits = 100;
        $this->bet = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getPseudo()
    {
        return $this->pseudo;
    }

    /**
     * @param mixed $pseudo
     */
    public function setPseudo($pseudo): void
    {
        $this->pseudo = $pseudo;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @param mixed $mail
     */
    public function setMail($mail): void
    {
        $this->mail = $mail;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * @param mixed $birthDate
     */
    public function setBirthDate($birthDate): void
    {
        $this->birthDate = $birthDate;
    }

    /**
     * @return float
     */
    public function getCredits(): float
    {
        return $this->credits;
    }

    /**
     * @param float $credits
     */
    public function setCredits(float $credits): void
    {
        $this->credits = $credits;
    }



    /**
     * @return array
     */
    public function getBet()
    {
        return $this->bet->getValues();
    }

    /**
     * @param Bet $bet
     */
    public function setBet(Bet $bet)
    {
        $this->bet->add($bet);
        $bet->setUser($this);

    }


}
