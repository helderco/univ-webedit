<?php

namespace Siriux\WebeditBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="menu_items")
 */
class MenuItem
{
    /**
     * @var integer $id
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Menu", inversedBy="items")
     * @ORM\JoinColumn(nullable=false)
     */
    private $menu;

    /**
     * @var string $title
     *
     * @ORM\Column(type="string", length=45)
     */
    private $title;


    /**
     * @var string $url
     *
     * @ORM\Column(type="string", length=45)
     */
    private $url;

    /**
     * @var string $weight
     *
     * @ORM\Column(type="integer")
     */
    private $weight;

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
     * Set url
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set weight
     *
     * @param integer $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * Get weight
     *
     * @return integer 
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set menu
     *
     * @param Siriux\WebeditBundle\Entity\Menu $menu
     */
    public function setMenu(\Siriux\WebeditBundle\Entity\Menu $menu)
    {
        $this->menu = $menu;
    }

    /**
     * Get menu
     *
     * @return Siriux\WebeditBundle\Entity\Menu 
     */
    public function getMenu()
    {
        return $this->menu;
    }
}