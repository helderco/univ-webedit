<?php

namespace Siriux\WebeditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Siriux\WebeditBundle\Entity\Page;


/**
 * Admin panel controller.
 *
 * @Route("/pages")
 */
class PageController extends Controller {

    /**
     *
     * @Route("/{id}/edit", name="edit_page")
     * @Template()
     */
    public function editAction($id)
    {
        // editar
        
        return array();
    }
    
    /**
     *
     * @Route("/list", name="list_page")
     * @Template()
     */
    public function listAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $repository = $this->getDoctrine()->getRepository('SiriuxWebeditBundle:Page');
        $page = $repository->findBy(array('user'=>$user->getId()));
        return array('pages'=>$page);
    }

     /**
     *
     * @Route("/new", name="new_page")
     * @Template()
     */
    public function newAction()
    {
               
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
        $page->setUser($user);
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($page);
        $em->flush();
        
        return $this->redirect($this->generateUrl('list_page'));
    }
    
    /**
     * Deletes a Page entity.
     *
     * @Route("/{id}/delete", name="delete_page")
     * @Template()
     */
    public function deleteAction($id)
    {   $em = $this->getDoctrine()->getEntityManager();
        $page = $em->getRepository('SiriuxWebeditBundle:Page')->find($id);
        $em->remove($page);
        $em->flush();
        return $this->redirect($this->generateUrl('list_page'));
    }
}
