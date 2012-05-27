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
     * @Route("/edit", name="edit_page")
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
    
    /**
     *
     * @Route("/create", name="create_page")
     * @Template()
     */
    public function createAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $page = new Page();
        $page->setTitle($_POST['p_title']);
        $page->setTemplate(new Template());
        $page->setUser($user);
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($page);
        $em->flush();
        
        return $this->redirect($this->generateUrl('edit_page'));
    }
}
