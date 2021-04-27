<?php

namespace App\Entity;

use App\Repository\BetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BetRepository::class)
 */
class Bet
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float", length=255)
     */
    private $oddsTotal;

    /**
     * @ORM\Column(type="float", length=255)
     */
    private $betAmount;

    /**
     * @ORM\Column(type="datetime")
     */
    private $betDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $betDateEnd;

    /**
     * @ORM\Column (type="text", length=255)
     */
    private $win;

    /**
     * @ORM\Column (type="text")
     */
    private $data;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="bet")
     */
    private $user;

    /**
     * Bet constructor.
     * @param $oddsTotal
     * @param $betAmount
     * @param $data
     * @param $betDateEnd
     * @param $user
     */
    public function __construct($oddsTotal, $betAmount, $data, $betDateEnd ,$user)
    {
        $this->oddsTotal = $oddsTotal;
        $this->betAmount = $betAmount;
        $this->betDate = new \DateTime();
        $this->betDateEnd = $betDateEnd;
        $this->win = 'in progress';
        $this->data = json_encode( $data );
        $this->user = $user;
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
    public function getOddsTotal()
    {
        return $this->oddsTotal;
    }

    /**
     * @param mixed $oddsTotal
     */
    public function setOddsTotal($oddsTotal): void
    {
        $this->oddsTotal = $oddsTotal;
    }

    /**
     * @return mixed
     */
    public function getBetAmount()
    {
        return $this->betAmount;
    }

    /**
     * @param mixed $betAmount
     */
    public function setBetAmount($betAmount): void
    {
        $this->betAmount = $betAmount;
    }

    /**
     * @return mixed
     */
    public function getBetDate()
    {
        return $this->betDate;
    }

    /**
     * @param mixed $betDate
     */
    public function setBetDate($betDate): void
    {
        $this->betDate = $betDate;
    }

    /**
     * @return mixed
     */
    public function getBetDateEnd()
    {
        return $this->betDateEnd;
    }

    /**
     * @param mixed $betDateEnd
     */
    public function setBetDateEnd($betDateEnd): void
    {
        $this->betDateEnd = $betDateEnd;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }


    /**
     * @return mixed
     */
    public function getData()
    {
        return json_decode( $this->data );
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }


    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }


    /**
     * @return string
     */
    public function getWin(): string
    {
        return $this->win;
    }

    /**
     * @param string $win
     */
    public function setWin(string $win): void
    {
        $this->win = $win;
    }



}
