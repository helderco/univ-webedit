<?php

namespace Siriux\WebeditBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template as Twig;

use Siriux\WebeditBundle\Form\Type\TemplateType;
use Siriux\WebeditBundle\Entity\Template;
use Siriux\UserBundle\Entity\User;

use Gaufrette\Filesystem;
use Gaufrette\Adapter\Local as LocalAdapter;

/**
 * @Route("/template")
 */
class TemplateController extends Controller
{
    /**
     * @Route("", name="templates")
     */
    public function indexAction()
    {
        return $this->redirect($this->generateUrl('template_list'));
    }

    /**
     * @Route("/list", name="template_list")
     * @Twig()
     */
    public function listAction()
    {
        $templates = $this->getTemplateRepository()->findAll();

        $deleteForms = array();

        foreach ($templates as $template) {
            $id = $template->getId();
            $deleteForms[$id] = $this->createDeleteForm($id)->createView();
        }

        return array(
            'templates' => $templates,
            'delete_forms' => $deleteForms,
        );
    }

    /**
     * @Route("/new", name="template_new")
     * @Twig()
     */
    public function newAction()
    {
        $filesystem = $this->getFilesystem('templates');
        $base = $filesystem->read('base.html');

        $template = new Template();
        $template->setBody($base);
        
        $form = $this->createForm(new TemplateType(), $template);

        return array(
            'template' => $template,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/create", name="template_create")
     * @Method("post")
     * @Twig("SiriuxWebeditBundle:Admin/Template:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $user = $this->get('security.context')->getToken()->getUser();

        $template = new Template();
        $template->setUser($user);
        
        $form = $this->createForm(new TemplateType(), $template);
        $form->bindRequest($request);
        
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($template);
            $em->flush();

            $this->get('session')->setFlash('success',
                    sprintf('A new template with id %d was added successfully',
                        $template->getId()));

            return $this->redirect($this->generateUrl('templates'));
        }

        return array(
            'template' => $template,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/{id}/edit", name="template_edit")
     * @Twig()
     */
    public function editAction($id)
    {
        $template = $this->getTemplate($id);

        $editForm = $this->createForm(new TemplateType(), $template);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'template'    => $template,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * @Route("/{id}/update", name="template_update")
     * @Method("post")
     * @Twig("SiriuxWebeditBundle:Admin/Template:edit.html.twig")
     */
    public function updateAction($id)
    {
        $template = $this->getTemplate($id);

        $editForm = $this->createForm(new TemplateType(), $template);
        $editForm->bindRequest($this->getRequest());

        if ($editForm->isValid()) {
            $em = $this->getEntityManager();

            $em->persist($template);
            $em->flush();

            $this->get('session')->setFlash('success',
                    'Your changes were saved!');

            return $this->redirect(
                $this->generateUrl('template_edit', array('id' => $id)));
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'template'    => $template,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * @Route("/{id}/delete", name="template_delete")
     * @Method("post")
     * @Twig()
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $form->bindRequest($this->getRequest());

        if ($form->isValid()) {
            $template = $this->getTemplate($id);

            $name = $template->getName();

            $em = $this->getEntityManager();
            $em->remove($template);
            $em->flush();

            $this->get('session')->setFlash('success',
                    "Template '$name' deleted successfully.");
        }

        return $this->redirect($this->generateUrl('templates'));
    }

    private function getFilesystem($alias)
    {
        return $this->get('knp_gaufrette.filesystem_map')->get($alias);
    }

    private function getTemplateRepository()
    {
        return $this->getEntityManager()
                    ->getRepository('SiriuxWebeditBundle:Template');
    }

    private function getEntityManager()
    {
        return $this->getDoctrine()->getEntityManager();
    }

    private function getTemplate($id) {
        $template = $this->getTemplateRepository()->find($id);

        if (!$template) {
            throw $this->createNotFoundException("Unable to find template with id $id.");
        }

        return $template;
    }
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
