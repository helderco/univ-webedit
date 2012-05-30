<?php

namespace Siriux\WebeditBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * User page.
 *
 * @ORM\Entity
 * @ORM\Table(name="pages")
 */
class Page
{
    /**
     * The page id.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * The page name, as used in a filename (without the extension).
     *
     * @ORM\Column(type="string", length=45)
     */
    private $name;

    /**
     * Template to use for layout/design of the page.
     *
     * @ORM\ManyToOne(targetEntity="Template")
     * @ORM\JoinColumn(nullable=false)
     */
    private $template;

    /**
     * List of blocks that make the content areas.
     *
     * @ORM\OneToMany(targetEntity="Block", mappedBy="page")
     */
    private $blocks;

    /**
     * Menu if available.
     *
     * @ORM\ManyToOne(targetEntity="Menu")
     */
    private $menu;

    /**
     * The owner or creator of the page.
     *
     * @ORM\ManyToOne(targetEntity="\Siriux\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * Constructor.
     */
    public function __construct($name = null)
    {
        $this->name = $name;
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
     * Get filename
     *
     * @return string 
     */
    public function getFilename($ext = 'html')
    {
        return $this->getName().'.'.$ext;
    }

    public function getPath($base_dir = 'pages')
    {
        return sprintf('/%s/user-%d/page-%d', $base_dir, $this->user->getId(), $this->id);
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
     * @param Siriux\WebeditBundle\Entity\Block $block
     */
    public function addBlock(\Siriux\WebeditBundle\Entity\Block $block)
    {
        $this->blocks[$block->getName()] = $block;
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
    
     /**
     * Get block
     *
     * @return Siriux\WebeditBundle\Entity\Block
     */
    public function getBlock($name)
    {
        foreach ($this->blocks as $block) {
            if ($block->getName() == $name) {
                return $block;
            }
        }
        $block = new Block();
        $block->setName($name);
        return $block;
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

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
}