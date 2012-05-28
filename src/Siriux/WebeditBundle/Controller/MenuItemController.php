<?php

namespace Siriux\WebeditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Siriux\WebeditBundle\Entity\MenuItem;



/**
 * Admin panel controller.
 *
 * @Route("/menu/{menu_id}/item")
 */
class MenuItemController extends Controller {

    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/list", name="item_list")
     * @Template()
     */
    public function listAction($menu_id)
    {   $repository = $this->getDoctrine()->getRepository('SiriuxWebeditBundle:MenuItem');
        $menu = $repository->findBy(array('menu'=>$menu_id),array('weight'=>'asc'));
        return array('itens'=>$menu, 'menu_id'=>$menu_id);
    }
    
    
     /**
     * Create a new Menu entity.
     *
     * @Route("/create", name="item_create")
     * @Template()
     */
    public function createAction($menu_id)
    {   
        $menu = $this->getDoctrine()->getRepository('SiriuxWebeditBundle:Menu')->
        findOneBy(array('id'=>$menu_id));      
        $menuItem = new MenuItem();
        $menuItem->setTitle($_POST['i_title']);
        $menuItem->setUrl($_POST['i_url']);
        $menuItem->setWeight($_POST['i_weight']);
        $menuItem->setMenu($menu);
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($menuItem);
        $em->flush();
        
        return $this->redirect($this->generateUrl('item_list', array('menu_id'=>$menu_id)));
    }
    
    
    /**
     * Deletes a menu entity.
     *
     * @Route("/{item_id}/delete", name="item_delete")
     * @Template()
     */
    public function deleteAction($menu_id,$item_id)
    {   $em = $this->getDoctrine()->getEntityManager();
        $menuItem = $em->getRepository('SiriuxWebeditBundle:MenuItem')->find($item_id);
        $em->remove($menuItem);
        $em->flush();
        return $this->redirect($this->generateUrl('item_list',array('menu_id'=>$menu_id)));
    }
    
    
}
