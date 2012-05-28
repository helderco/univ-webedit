<?php

namespace Siriux\WebeditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Siriux\WebeditBundle\Entity\Menu;



/**
 * Admin panel controller.
 *
 * @Route("/menu")
 */
class MenuController extends Controller {
  
    /**
     * Create a new Menu entity.
     *
     * @Route("/create", name="menu_create")
     * @Template()
     */
    public function createAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $menu = new Menu();
        $menu->setTitle($_POST['m_title']);
        $menu->setUser($user);
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($menu);
        $em->flush();
        
        return $this->redirect($this->generateUrl('menu_edit'));
    }
    
    
    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/edit", name="menu_edit")
     * @Template()
     */
    public function editAction()
    {   $user = $this->get('security.context')->getToken()->getUser();
        $repository = $this->getDoctrine()->getRepository('SiriuxWebeditBundle:Menu');
        $menu = $repository->findBy(array('user'=>$user->getId()));
        return array('menus'=>$menu);
    }
    
    /**
     * Deletes a menu entity.
     *
     * @Route("/{id}/delete", name="menu_delete")
     * @Template()
     */
    public function deleteAction($id)
    {   $em = $this->getEm();
        $menu = $this->getRepository('Menu')->find($id);
        $menuItens = $this->getRepository('MenuItem')
                        ->findBy(array('menu'=>$id));
        
        foreach($menuItens as $item){
            $em->remove($item);
        }
        $em->remove($menu);
        $em->flush();
        return $this->redirect($this->generateUrl('menu_edit'));
    }
    
    private function getRepository($entity){
         return $this->getEm()
                     ->getRepository('SiriuxWebeditBundle:'.$entity);
     }
     private function getEm(){
         return $this->getDoctrine()
                     ->getEntityManager();   
     }
}
