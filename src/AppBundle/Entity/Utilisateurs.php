<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints as Recaptcha;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="utilisateurs")
 */
class Utilisateurs extends BaseUser
{
    public function __construct()
    {
        parent::__construct();
        $this->media = new \Doctrine\Common\Collections\ArrayCollection();
        $this->commandes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->adresses = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook_id", type="string", nullable=true)
     */
    protected $facebook_id;

    /**
     * @Recaptcha\IsTrue
     */
    public $recaptcha;







    /**
     * Set facebookId
     *
     * @param string $facebookId
     *
     * @return Utilisateurs
     */
    public function setFacebookId($facebookId)
    {
        $this->facebook_id = $facebookId;

        return $this;
    }

    /**
     * Get facebookId
     *
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebook_id;
    }
}
