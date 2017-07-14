<?php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="bonobo")
 */
class Bonobo extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Bonobo")
     * @ORM\JoinTable(name="friends",
     *     joinColumns={@ORM\JoinColumn(name="a", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="b", referencedColumnName="id")}
     * )
     */
    private $friends;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Add friend
     *
     * @param \AppBundle\Entity\Bonobo $friend
     *
     * @return Bonobo
     */
    public function addFriend(\AppBundle\Entity\Bonobo $friend)
    {
        if (!$this->friends->contains($friend)) {
            $this->friends->add($friend);
            $friend->addFriend($this);
        }

        return $this;
    }

    /**
     * Remove friend
     *
     * @param \AppBundle\Entity\Bonobo $friend
     */
    public function removeFriend(\AppBundle\Entity\Bonobo $friend)
    {
        if ($this->friends->contains($friend)) {
            $this->friends->removeElement($friend);
            $friend->removeFriend($this);
        }
    }

    /**
     * Get friends
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFriends()
    {
        return $this->friends;
    }

}