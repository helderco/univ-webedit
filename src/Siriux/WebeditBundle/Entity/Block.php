<?php

namespace Siriux\WebeditBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Represents a content area of a page.
 *
 * @ORM\Entity
 * @ORM\Table(name="blocks")
 */
class Block
{
    /** Block type for an <input type="text"> element. */
    const TYPE_STRING = 'string';

    /** Block type for an <img> element. */
    const TYPE_IMAGE = 'image';

    /** Block type for a <textarea> element. */
    const TYPE_TEXT = 'text';

    /**
     * The block id.
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Machine name for the block.
     *
     * @ORM\Column(type="string", length=45)
     */
    private $name;

    /**
     * Block type, mapped to an html element.
     *
     * @ORM\Column(type="string", length="12")
     */
    private $type;

    /**
     * The page this block belongs to.
     *
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="blocks")
     */
    private $page;

    /**
     * Block content.
     *
     * In case of an image type, the content is the image file name.
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank()
     */
    private $content;

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

    /**
     * Set page
     *
     * @param Siriux\WebeditBundle\Entity\Page $page
     */
    public function setPage(\Siriux\WebeditBundle\Entity\Page $page)
    {
        $this->page = $page;
    }

    /**
     * Get page
     *
     * @return Siriux\WebeditBundle\Entity\Page 
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set content
     *
     * @param text $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return text 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }
}