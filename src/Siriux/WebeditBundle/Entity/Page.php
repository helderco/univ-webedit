<?php

namespace Siriux\WebeditBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="pages")
 */
class Page
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="Template")
     */
    private $template;

    /**
     * @ORM\OneToMany(targetEntity="Block", mappedBy="page")
     */
    private $blocks;


    /**
     * @ORM\ManyToOne(targetEntity="\Siriux\UserBundle\Entity\User")
     */
    private $user;

    public function __construct()
    {
        $this->blocks = new ArrayCollection();
    }


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
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set template
     *
     * @param Siriux\WebeditBundle\Entity\Template $template
     */
    public function setTemplate(\Siriux\WebeditBundle\Entity\Template $template)
    {
        $this->template = $template;
    }

    /**
     * Get template
     *
     * @return Siriux\WebeditBundle\Entity\Template 
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set user
     *
     * @param Siriux\UserBundle\Entity\User $user
     */
    public function setUser(\Siriux\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return Siriux\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add blocks
     *
     * @param Siriux\WebeditBundle\Entity\Block $blocks
     */
    public function addBlock(\Siriux\WebeditBundle\Entity\Block $blocks)
    {
        $this->blocks[] = $blocks;
    }

    /**
     * Get blocks
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getBlocks()
    {
        return $this->blocks;
    }
}
