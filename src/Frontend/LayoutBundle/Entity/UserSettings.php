<?php

namespace Frontend\LayoutBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserSettings
 *
 * @ORM\Table(name="user_settings")
 * @ORM\Entity
 */
class UserSettings
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $userId;

     /**
     * @ORM\OneToOne(targetEntity="\Frontend\LayoutBundle\Entity\User", inversedBy="userSettings")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    

    /**
     * @var integer
     *
     * @ORM\Column(name="avatar_id", type="bigint", nullable=true)
     */
    private $avatarId;

    /**
     * @var integer
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="gender", type="string", nullable=true)
     */
    private $gender;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birth_date", type="datetime", nullable=true)
     */
    private $birthDate;

    /**
     * @var string
     *
     * @ORM\Column(name="my_uploads_visit", type="string", nullable=true)
     */
    private $myUploadsVisit;

    /**
     * @var string
     *
     * @ORM\Column(name="messsage_to_me", type="string", nullable=true)
     */
    private $messsageToMe;

    /**
     * @var string
     *
     * @ORM\Column(name="style", type="string", length=255, nullable=true)
     */
    private $style;



    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     * @return UserSettings
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set avatarId
     *
     * @param integer $avatarId
     * @return UserSettings
     */
    public function setAvatarId($avatarId)
    {
        $this->avatarId = $avatarId;

        return $this;
    }

    /**
     * Get avatarId
     *
     * @return integer 
     */
    public function getAvatarId()
    {
        return $this->avatarId;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return UserSettings
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set gender
     *
     * @param string $gender
     * @return UserSettings
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string 
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set birthDate
     *
     * @param \DateTime $birthDate
     * @return UserSettings
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * Get birthDate
     *
     * @return \DateTime 
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * Set myUploadsVisit
     *
     * @param string $myUploadsVisit
     * @return UserSettings
     */
    public function setMyUploadsVisit($myUploadsVisit)
    {
        $this->myUploadsVisit = $myUploadsVisit;

        return $this;
    }

    /**
     * Get myUploadsVisit
     *
     * @return string 
     */
    public function getMyUploadsVisit()
    {
        return $this->myUploadsVisit;
    }

    /**
     * Set messsageToMe
     *
     * @param string $messsageToMe
     * @return UserSettings
     */
    public function setMesssageToMe($messsageToMe)
    {
        $this->messsageToMe = $messsageToMe;

        return $this;
    }

    /**
     * Get messsageToMe
     *
     * @return string 
     */
    public function getMesssageToMe()
    {
        return $this->messsageToMe;
    }

    /**
     * Set style
     *
     * @param string $style
     * @return UserSettings
     */
    public function setStyle($style)
    {
        $this->style = $style;

        return $this;
    }

    /**
     * Get style
     *
     * @return string 
     */
    public function getStyle()
    {
        return $this->style;
    }


    /**
     * Set user
     *
     * @param \Frontend\LayoutBundle\Entity\User $user
     * @return UserSettings
     */
    public function setUser(\Frontend\LayoutBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Frontend\LayoutBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}