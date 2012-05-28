<?php

namespace Siriux\WebeditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template as Twig;
use Siriux\WebeditBundle\Entity\Page;
use Siriux\WebeditBundle\Entity\Template;
use Siriux\WebeditBundle\Entity\Block;
use Siriux\WebeditBundle\Entity\Menu;

use Gaufrette\File;
use Gaufrette\Filesystem;
use Gaufrette\Adapter\Local as LocalAdapter;
use Gaufrette\Adapter\Zip as ZipAdapter;

/**
 * Admin panel controller.
 *
 * @Route("/pages")
 */
class PageController extends Controller {

    /**
     *
     * @Route("/{id}/edit", name="edit_page")
     * @Twig()
     */
    public function editAction($id)
    {
        $page = $this->getRepository('Page')->find($id);
        $menus = $this->getRepository('Menu')->findAll();
        return array('menus'=>$menus , 'page'=>$page);
    }
    
    
    /**
     *
     * @Route("/{id}/update", name="update_page")
     * @Twig()
     */
    public function updateAction($id){
        $em = $this->getEm();
                       
        $page = $this->getRepository('Page')->find($id);
        
        $title = $page->getBlock('header_title');
        $title->setType(Block::TYPE_STRING);
        $title->setContent($_POST['header_title']);
        $title->setPage($page);
        $em->persist($title);
        
        $body = $page->getBlock('p_body');
        $body->setType(Block::TYPE_TEXT);
        $body->setContent($_POST['p_body']);
        $body->setPage($page);
        $em->persist($body);
        
        $footer = $page->getBlock('p_footer');
        $footer->setType(Block::TYPE_TEXT);
        $footer->setContent($_POST['p_footer']);
        $footer->setPage($page);
        $em->persist($footer);
        
        $menu = $this->getRepository('Menu')->find($_POST['p_menu']);
        if($menu){
            $page->setMenu($menu);
        }
        
        $em->persist($page);
        $em->flush();
        $this->get('session')->setFlash('success','Page content updated');
        return $this->redirect($this->generateUrl('edit_page', array('id'=>$id)));
        
    }
    
    /**
     *
     * @Route("/list", name="list_page")
     * @Twig()
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
     * @Twig()
     */
    public function newAction()
    {
        $templates = $this->getRepository('Template')->findAll();
        return array('templates'=>$templates);
    }
    
    /**
     *
     * @Route("/create", name="create_page")
     * @Twig()
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
        
        return $this->redirect($this->generateUrl('edit_page',array('id'=>$page->getId())));
    }
    
    /**
     *
     * @Route("/{id}/preview", name="preview_page")
     */
    public function previewAction($id)
    {
        $page = $this->getRepository('Page')->find($id);
        return new Response($this->getPageContent($page));   
    }
    
    private function getPageContent(Page $page)
    {
        $page = $this->getRepository('Page')->find($id);
        $body = $page->getBlock('p_body')->getContent();
        $footer = $page->getBlock('p_footer')->getContent();
        $header_title = $page->getBlock('header_title')->getContent();
        
        $menu = $this->renderMenuAction($page->getMenu()->getId());

        $params = array(
            'base_dir' => "/templates/".$page->getTemplate()->getId(),
            'page' => $page,
            'body'=>$body, 
            'menu'=> $menu,
            'title'=> $header_title,
            'footer' => $footer,
        );

        return $this->get('twigstring')->render(
            $page->getTemplate()->getBody(), $params
        );        
    }
    
    /**
     * @Route("/{id}/download", name="page_download")
     */
    public function downloadAction($id, $contentOnly = false)
    {
        $page = $this->getRepository('Page')->find($id);
        $template = $page->getTemplate();
        
        $localPath = $this->container->getParameter('templates_dir')."/".$template->getId();
        $zipPublicPath = '/pages/'.$page->getId().'/webedit.zip';
        $zipPath = realpath($this->container->getParameter('kernel.root_dir').'/../web'.$zipPublicPath);
        
        $adapterLocal = new LocalAdapter($localPath);
        $local = new Filesystem($adapterLocal);
        
        $zipAdapter = new ZipAdapter($zipPath);
        $zip = new Filesystem($zipAdapter);
        
        $zip->write('index.html', $template->getBody());
        
        foreach ($local->keys() as $key) {
            $zip->write($key, $local->read($key));
        }
        
        header('Location: '.$zipPublicPath);
        exit;
    }
    
    /**
     * Deletes a Page entity.
     *
     * @Route("/{id}/delete", name="delete_page")
     * @Twig()
     */
    public function deleteAction($id)
    {   $em = $this->getDoctrine()->getEntityManager();
        $page = $em->getRepository('SiriuxWebeditBundle:Page')->find($id);
        $em->remove($page);
        $em->flush();
        return $this->redirect($this->generateUrl('list_page'));
    }
    
    private function getRepository($entity){
         return $this->getEm()
                     ->getRepository('SiriuxWebeditBundle:'.$entity);
     }
     private function getEm(){
         return $this->getDoctrine()
                     ->getEntityManager();   
     }
     
    /**
     * Renders a menu into a string.
     */
     public function renderMenuAction($id, $class = null)
     {
         return $this->render('SiriuxWebeditBundle:Page:renderMenu.html.twig', array(
             'items'=>$this->getRepository('MenuItem')->findBy(
                     array('menu'=>$id),
                     array('weight'=>'asc')),
             'class' => $class
            ))->getContent();
     }
     
     
}
