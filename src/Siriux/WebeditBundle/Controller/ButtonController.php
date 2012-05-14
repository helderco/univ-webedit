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
 * @Route("/button")
 */
class ButtonController extends Controller {

    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/edit", name="button_edit")
     * @Template()
     */
    public function editAction()
    {
             
        return array();
    }

}
