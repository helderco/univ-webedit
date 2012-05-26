<?php

namespace Siriux\WebeditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;



/**
 * Admin panel controller.
 *
 * @Route("/menu")
 */
class MenuController extends Controller {

    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/new", name="menu_new")
     * @Template()
     */
    public function newAction()
    {
             
        return array();
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/edit", name="menu_edit")
     * @Template()
     */
    public function editAction()
    {
             
        return array();
    }
}
