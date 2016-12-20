<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User extends BaseUser {

    public function __construct() {
        parent::__construct();
        $this->friends = new ArrayCollection();
        $this->friendsWithMe = new ArrayCollection();
        $this->summoners = new ArrayCollection();
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="friendsWithMe")
     * @ORM\JoinTable(
     *  name="friends",
     *  joinColumns={@ORM\JoinColumn(name="user_id" , referencedColumnName="id")},
     *  inverseJoinColumns={
     *      @ORM\JoinColumn(name="friend_user_id",
     *      referencedColumnName="id")
     *  }
     * )
     * */
    private $friends;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="friends")
     * */
    private $friendsWithMe;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Summoner\Summoner", mappedBy="user")
     */
    private $summoners;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Add friends
     *
     * @param \AppBundle\Entity\User $friends
     * @return User
     */
    public function addFriend(\AppBundle\Entity\User $friends) {
        $this->friends[] = $friends;

        return $this;
    }

    /**
     * Remove friends
     *
     * @param \AppBundle\Entity\User $friends
     */
    public function removeFriend(\AppBundle\Entity\User $friends) {
        $this->friends->removeElement($friends);
    }

    /**
     * Get friends
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFriends() {
        return $this->friends;
    }

    /**
     * Add friendsWithMe
     *
     * @param \AppBundle\Entity\User $friendsWithMe
     * @return User
     */
    public function addFriendsWithMe(\AppBundle\Entity\User $friendsWithMe) {
        $this->friendsWithMe[] = $friendsWithMe;

        return $this;
    }

    /**
     * Remove friendsWithMe
     *
     * @param \AppBundle\Entity\User $friendsWithMe
     */
    public function removeFriendsWithMe(\AppBundle\Entity\User $friendsWithMe) {
        $this->friendsWithMe->removeElement($friendsWithMe);
    }

    /**
     * Get friendsWithMe
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFriendsWithMe() {
        return $this->friendsWithMe;
    }

    /**
     * hasFriend friend
     *
     * @param \AppBundle\Entity\User $friend
     * @return boolean
     */
    public function hasFriend(\AppBundle\Entity\User $friend) {
        return $this->friends->contains($friend);
    }

    /**
     * canAddFriend friend
     *
     * @param \AppBundle\Entity\User $friend
     * @return boolean
     */
    public function canAddFriend(\AppBundle\Entity\User $friend) {
        return $this != $friend && !$this->hasFriend($friend);
    }


    /**
     * Add summoner
     *
     * @param \AppBundle\Entity\Summoner\Summoner $summoner
     *
     * @return User
     */
    public function addSummoner(\AppBundle\Entity\Summoner\Summoner $summoner)
    {
        $this->summoners[] = $summoner;

        return $this;
    }

    /**
     * Remove summoner
     *
     * @param \AppBundle\Entity\Summoner\Summoner $summoner
     */
    public function removeSummoner(\AppBundle\Entity\Summoner\Summoner $summoner)
    {
        $this->summoners->removeElement($summoner);
    }

    /**
     * Get summoners
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSummoners()
    {
        return $this->summoners;
    }
}
