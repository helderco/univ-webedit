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
        $user = $this->get('security.context')->getToken()->getUser();
        $menus = $this->getRepository('Menu')->findBy(array('user' => $user->getId()));
        return array('menus'=>$menus , 'page'=>$page);
    }
    
    
    /**
     *
     * @Route("/{id}/update", name="update_page")
     * @Twig()
     */
    public function updateAction($id)
    {
        $em = $this->getEm();
        $page = $this->getPage($id);
        
        $title = $page->getBlock('p_title');
        $title->setType(Block::TYPE_STRING);
        $title->setContent($_POST['p_title']);
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
        if (empty($_POST['template']) || empty($_POST['p_name'])) {
            $this->get('session')->setFlash('error', 'All fields are required.');
            return $this->redirect($this->generateUrl('new_page'));
        }

        $template = $this->getRepository('Template')->find($_POST['template']);
        $user = $this->get('security.context')->getToken()->getUser();
        $page = new Page($_POST['p_name']);
        $page->setTemplate($template);
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
        return new Response($this->getPageContent($id));
    }
    
    private function getPageContent($page)
    {
        if (is_numeric($page)) {
            $page = $this->getPage($page);
        }

        $title = $page->getBlock('p_title')->getContent();
        $body = $page->getBlock('p_body')->getContent();
        $footer = $page->getBlock('p_footer')->getContent();

        $menu = '';
        if ($page->getMenu()) {
            $menu = $this->renderMenuAction($page->getMenu()->getId());
        }

        $params = array(
            'base_dir' => "/templates/".$page->getTemplate()->getId(),
            'page' => $page,
            'body'=>$body, 
            'menu'=> $menu,
            'title'=> $title,
            'footer' => $footer,
        );

        return $this->get('twigstring')->render(
            $page->getTemplate()->getBody(), $params
        );        
    }
    
    /**
     * @Route("/{id}/download.zip", name="download_page_zip")
     */
    public function downloadZipAction($id)
    {
        $page = $this->getPage($id);

        // setup dirs and paths (path is for the URL and dir is for the filesystem)
        $templatesDir = $this->container->getParameter('templates_dir');
        $templateDir = $templatesDir.'/'.$page->getTemplate()->getId();

        $zipFile = $page->getFilename('zip');     // the name of the zip file
        $zipPath = $page->getPath().'/'.$zipFile; // the path to the zip file (URL)
        $pageDir = $this->getPageDir($page);      // the absolute zip file directory (filesystem)
        $zipDir = $pageDir.'/'.$zipFile;          // the absolute zip file location (filesystem)

        // create page dir if doesn't exist and delete zip file if previously created
        $adapterPage = new LocalAdapter($pageDir, true);
        if ($adapterPage->exists($zipFile)) {
            $adapterPage->delete($zipFile);
        }

        // get the template's files
        $adapterTemplate = new LocalAdapter($templateDir);
        $template = new Filesystem($adapterTemplate);

        // setup a zip container
        $zipAdapter = new ZipAdapter($zipDir);
        $zip = new Filesystem($zipAdapter);

        // write files to zip
        $zip->write($page->getFilename(), $this->getPageContent($page));
        foreach ($template->keys() as $key) {
            $zip->write($key, $template->read($key));
        }

        return $this->redirect($zipPath);
    }
    
     /**
     *
     * @Route("/{id}/download", name="download_page")
     */
    public function downloadAction($id)
    {
        $page = $this->getPage($id);

        // like preview but force download
        return new Response(
            $this->getPageContent($page, $id),
            200,
            array(
                'Content-type' => 'application/force-download',
                'Content-Disposition' => sprintf(
                        'attachment; filename="%s"', $page->getFilename()
                )
            )
        );
    }
    
    
    /**
     * Deletes a Page entity.
     *
     * @Route("/{id}/delete", name="delete_page")
     * @Twig()
     */
    public function deleteAction($id)
    {
        $page = $this->getPage($id);
        $name = $page->getFilename();

        foreach ($page->getBlocks() as $block) {
            $this->getEm()->remove($block);
        }

        $this->deleteDir($page);
        
        $this->getEm()->remove($page);
        $this->getEm()->flush();

        $this->get('session')->setFlash(
                'success', "Page '$name' deleted successfully.");

        return $this->redirect($this->generateUrl('list_page'));
    }
    
    private function getRepository($entity)
    {
         return $this->getEm()
                     ->getRepository('SiriuxWebeditBundle:'.$entity);
    }
    
    private function getEm()
    {
         return $this->getDoctrine()
                     ->getEntityManager();   
    }

    private function getPage($id)
    {
        return $this->getRepository('Page')->find($id);
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

    private function getPageDir(Page $page)
    {
        return realpath($this->container->getParameter('kernel.root_dir').'/../web').$page->getPath();
    }
     
    /**
     * Recursively delete a directory.
     *
     * @param Page
     */
    private function deleteDir(Page $page)
    {
        $dir = $this->getPageDir($page);

        if (is_dir($dir)) {
            $it = new \RecursiveDirectoryIterator($dir);
            $files = new \RecursiveIteratorIterator($it,
                        \RecursiveIteratorIterator::CHILD_FIRST);

            foreach($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }

            rmdir($dir);
        }
    }
}
