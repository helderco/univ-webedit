<?php

namespace Siriux\WebeditBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File as FileInfo;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template as Twig;

use Siriux\WebeditBundle\Form\Type\TemplateType;
use Siriux\WebeditBundle\Entity\Template;
use Siriux\UserBundle\Entity\User;

use Gaufrette\File;
use Gaufrette\Filesystem;
use Gaufrette\Adapter\Local as LocalAdapter;

/**
 * Administration for Templates.
 *
 * @Route("/template")
 */
class TemplateController extends Controller
{
    /**
     * Root for this section.
     *
     * @Route("", name="templates")
     */
    public function indexAction()
    {
        return $this->redirect($this->generateUrl('template_list'));
    }

    /**
     * Listing of templates.
     *
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
     * Form for adding a new template.
     *
     * @Route("/new", name="template_new")
     * @Twig()
     */
    public function newAction()
    {
        // base.html has the default content for new templates
        $filesystem = $this->getFileSystem();
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
     * Target form action for inserting a new template.
     *
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

            return $this->redirect($this->generateUrl('template_edit',
                    array('id' => $template->getId())));
        }

        return array(
            'template' => $template,
            'form' => $form->createView(),
        );
    }

    /**
     * Form for editing a template.
     *
     * @Route("/{id}/edit", name="template_edit", requirements={"id" = "\d+"})
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
     * Target form action for updating a template.
     *
     * @Route("/{id}/update", name="template_update", requirements={"id" = "\d+"})
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
     * Delete a template and related assets.
     *
     * @Route("/{id}/delete", name="template_delete", requirements={"id" = "\d+"})
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

            // delete assets
            $this->deleteDir($id);

            // delete database record
            $em = $this->getEntityManager();
            $em->remove($template);
            $em->flush();

            $this->get('session')->setFlash('success',
                    "Template '$name' deleted successfully.");
        }

        return $this->redirect($this->generateUrl('templates'));
    }

    /**
     * Target form action for uploading an asset.
     *
     * @Route("/{id}/edit/upload", name="template_upload", requirements={"id" = "\d+"})
     * @Method("post")
     * @Twig()
     */
    public function uploadAssetAction($id)
    {
        // calling getTemplate() ensures the associated template exists
        $template = $this->getTemplate($id);

        $form = $this->createUploadForm();

        $form->bindRequest($this->getRequest());

        if ($form->isValid()) {
            $file = $form['asset']->getData();

            if ($file) {
                // attempt to place css, js and images in their own folders
                $dir = $this->container->getParameter("templates_dir") . "/$id";
                $extension = $file->guessExtension();

                if (!$extension || $extension == 'txt') {
                    $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                }

                switch ($extension) {
                    case 'css':
                    case 'js':
                        $dir .= '/'.$extension;
                        break;
                    case 'jpg':
                    case 'png':
                    case 'gif':
                        $dir .= '/images';
                        break;
                }

                $file->move($dir, $file->getClientOriginalName());

                $this->get('session')->setFlash('success',
                        "File '".$file->getClientOriginalName()."' uploaded successfully.");
            }
            else {
                $this->get('session')->setFlash('error', "No valid file was uploaded.");
            }
        }

        return $this->redirect($this->generateUrl('template_edit', array('id' => $id)));
    }

    /**
     * Controller for a partial to show the template's assets and upload asset form.
     *
     * @Twig()
     */
    public function assetsAction($id)
    {
        return array(
            'assets' => $this->getAssets($id),
            'template' => $this->getTemplate($id),
            'upload_form' => $this->createUploadForm()->createView(),
        );
    }

    /**
     * Form for editing/updating an asset or viewing.
     *
     * @Route("/{id}/edit/asset/{file}", name="template_asset", requirements={"id" = "\d+", "file" = ".+"})
     * @Twig()
     */
    public function assetAction($id, $file)
    {
        $editForm = null;

        $template = $this->getTemplate($id);
        
        $filesystem = $this->getFileSystem($id);
        $asset = new File($file, $filesystem);

        // update content if a post was made (happens on text files)
        if ($this->getRequest()->getMethod() == 'POST') {
            $form = $this->createAssetForm($asset);
            $form->bindRequest($this->getRequest());

            if ($form->isValid()) {
                $content = $form['content']->getData();

                $asset->setContent($content);

                $this->get('session')->setFlash('success', 'Asset updated.');

                return $this->redirect($this->generateUrl(
                        'template_asset', array('id'=>$id, 'file'=>$file)));
            }
            else {
                $this->get('session')->setFlash('error', 'Asset update not valid.');
            }
        }

        // find out if it's an image because in that case
        // we show it and don't allow editing.
        $image = false;
        $content = $asset->getContent();

        // getimagesize fails with a read error if the file is empty
        if (!empty($content)) {
            $image = getimagesize($this->getPath($id, $file));
        }

        if ($image !== false) {
            // the web path will be needed for the <img> src argument
            $image['path'] = basename($this->getPath()).'/'.$id.'/'.$file;
        }    
        else {
            // if not an image, present the form to edit content
            $editForm = $this->createAssetForm($asset)->createView();
        }

        return array(
            'asset' => $asset,
            'template' => $template,
            'image' => $image,
            'edit_form' => $editForm,
            'delete_form' => $this->createDeleteAssetForm($id, $file)->createView(),
        );
    }

    /**
     * Deletes an asset from a template.
     *
     * @Route("/{id}/asset/delete/{file}", name="template_asset_delete", requirements={"id"="\d+", "file"=".+"})
     * @Method("post")
     * @Twig()
     */
    public function deleteAssetAction($id, $file)
    {
        $form = $this->createDeleteAssetForm($id, $file);
        $form->bindRequest($this->getRequest());

        if ($form->isValid()) {
            $filesystem = $this->getFileSystem($id);

            try {
                $filesystem->delete($file);
                $this->get('session')->setFlash(
                    'success', 'File '.$file.' deleted from template '.$id);
            }
            catch (\InvalidArgumentException $e) {
                $this->get('session')->setFlash(
                    'error', 'Invalid file '.$file.', for template '.$id);
            }
        }

        return $this->redirect($this->generateUrl('template_edit', array('id' => $id)));
    }

    /**
     * Returns Doctrine's repository for Template objects.
     */
    private function getTemplateRepository()
    {
        return $this->getEntityManager()
                    ->getRepository('SiriuxWebeditBundle:Template');
    }

    /**
     * Returns Doctrine's entity manager.
     */
    private function getEntityManager()
    {
        return $this->getDoctrine()->getEntityManager();
    }

    /**
     * Returns the Template object from its id.
     *
     * @param int $id The template id.
     * @return Siriux\WebeditBundle\Entity\Template
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function getTemplate($id) {
        $template = $this->getTemplateRepository()->find($id);

        if (!$template) {
            throw $this->createNotFoundException(
                    "Unable to find template with id $id.");
        }

        return $template;
    }

    /**
     * Form for template deletion.
     *
     * @param int $id
     * @return Form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }

    /**
     * Form for uploading an asset.
     *
     * @return Form
     */
    private function createUploadForm()
    {
        return $this->createFormBuilder()
            ->add('asset', 'file')
            ->getForm()
        ;
    }

    /**
     * Form for editing a text based asset.
     *
     * @param File $file
     * @return Form
     */
    private function createAssetForm(File $file)
    {
        return $this->createFormBuilder(array('content' => $file->getContent()))
            ->add('content', 'textarea', array('attr'=>array('rows'=>18)))
            ->getForm()
        ;
    }

    /**
     * Form for deleting an asset.
     *
     * @param int $id the template id.
     * @param string $file the asset's filename.
     * @return Form
     */
    private function createDeleteAssetForm($id, $file)
    {
        return $this->createFormBuilder(array('id'=>$id, 'file'=>$file))
            ->add('id', 'hidden')
            ->add('file', 'hidden')
            ->getForm()
        ;
    }

    /**
     * Get templates path.
     *
     * If id is given, will return the template's path.
     * If file is given, will return the file path to the asset.
     *
     * @param int $id the template's id.
     * @param string $file the asset's file name.
     * @return string the full path
     */
    private function getPath($id = null, $file = null)
    {
        $dir = $this->container->getParameter("templates_dir");

        if ($id) {
            $dir .= '/'.$id;
        }

        if ($file) {
            $dir .= '/'.$file;
        }

        return realpath($dir);
    }

    /**
     * Recursively delete a directory.
     *
     * Used in removing an assets dir when deleting a template.
     *
     * @param int $id the template's id.
     */
    private function deleteDir($id)
    {
        $dir = $this->getPath($id);

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

    /**
     * Returns a Gaufrette filesystem abastraction object.
     *
     * If id is given, will return the filesystem for a template.
     *
     * @param int $id a template's id.
     * @param boolean $create weather to create the dir or not, if it doesn't exist.
     * @return \Gaufrette\Filesystem
     */
    private function getFileSystem($id = null, $create = true)
    {
        $adapter = new LocalAdapter($this->getPath($id), $create);

        return new Filesystem($adapter);
    }

    /**
     * Returns a list of assets belonging to a template.
     *
     * @param int $id the template's id.
     * @return array of \Gaufrette\File objects.
     */
    private function getAssets($id)
    {
        $assets = array();

        try {
            $filesystem = $this->getFileSystem($id, false);

            foreach ($filesystem->keys() as $key) {
                $file = new File($key, $filesystem);

                $assets[] = $file;
            }
        }
        catch (\RuntimeException $e) {
            // do nothing
        }

        return $assets;
    }
}
