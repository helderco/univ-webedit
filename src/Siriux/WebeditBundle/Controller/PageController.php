<?php

namespace Siriux\WebeditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;



/**
 * Admin panel controller.
 *
 * @Route("/pages")
 */
class PageController extends Controller {

    /**
     *
     * @Route("/edit", name="page_edit")
     * @Template()
     */
    public function editAction()
    {
        // editar
        
        return array();
    }

     /**
     *
     * @Route("/new", name="new_page")
     * @Template()
     */
    public function newAction()
    {
        // nova pagina
        
        return array();
    }
}
