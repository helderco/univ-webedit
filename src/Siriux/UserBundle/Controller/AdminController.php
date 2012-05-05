<?php

/**
 * This file is part of the Siriux package.
 *
 * (c) Helder Correia <helder.mc@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Siriux\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Siriux\UserBundle\Entity\User;
use Siriux\UserBundle\Form\Type\UserType;

/**
 * Admin panel controller.
 *
 * @Route("/admin/users")
 */
class AdminController extends Controller
{
    /**
     * Lists all User entities.
     *
     * @Route("", name="users")
     * @Template()
     */
    public function indexAction()
    {
        $users = $this->getUserManager()->findUsers();
        
        $users_list = array();
        $admins_list = array();
        $delete_forms = array();

        // we need two lists for users and admins to make separate tabs in the listing
        foreach ($users as $user) {
            if ($user->hasRole('ROLE_ADMIN') || $user->isSuperAdmin()) {
                array_push($admins_list, $user);
            } else {
                array_push($users_list, $user);
            }
            $delete_forms[$user->getId()] = $this->createDeleteForm($user->getId())->createView();
        }

        return array(
            'users'  => $users_list,
            'admins' => $admins_list,
            'delete_forms' => $delete_forms,
        );
    }

    /**
     * Displays a form to create a new User entity.
     *
     * @Route("/new", name="users_new")
     * @Template()
     */
    public function newAction()
    {
        $user = $this->getUserManager()->createUser()->setEnabled(true);
        $form = $this->createForm(new UserType('AdminNew'), $user);

        return array(
            'user' => $user,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a new User entity.
     *
     * @Route("/create", name="users_create")
     * @Method("post")
     * @Template("SiriuxUserBundle:Admin:new.html.twig")
     */
    public function createAction()
    {
        $userManager = $this->getUserManager();
        $user = $userManager->createUser();

        $form = $this->createForm(new UserType('AdminNew'), $user);
        $form->bindRequest($this->getRequest());

        if ($form->isValid()) {
            $userManager->updateUser($user);
            $this->get('session')->setFlash('success',
                    sprintf('A new user with id %d was created successfully', $user->getId()));

            return $this->redirect($this->generateUrl('users'));
        }

        return array(
            'user' => $user,
            'form' => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/{id}/edit", name="users_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $user = $this->getUser($id);
        $isCurrentUser = $this->isCurrentUser($user);

        if ($isCurrentUser) {
            return $this->redirect($this->generateUrl('fos_user_profile_edit'));
        }

        $editForm = $this->createForm(new UserType('AdminEdit', $isCurrentUser), $user);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'user'          => $user,
            'edit_form'     => $editForm->createView(),
            'delete_form'   => $deleteForm->createView(),
        );
    }

    /**
     * Updates an existing User entity.
     *
     * @Route("/{id}/update", name="users_update")
     * @Method("post")
     * @Template("SiriuxUserBundle:Admin:edit.html.twig")
     */
    public function updateAction($id)
    {
        $user = $this->getUser($id);
        $isCurrentUser = $this->isCurrentUser($user);

        $editForm = $this->createForm(new UserType('AdminEdit', $isCurrentUser), $user);
        $editForm->bindRequest($this->getRequest());

        if ($editForm->isValid()) {
            $this->getUserManager()->updateUser($user);
            $this->get('session')->setFlash('success', 'Your changes were saved!');

            return $this->redirect($this->generateUrl('users_edit', array('id' => $id)));
        } 

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'user'          => $user,
            'edit_form'     => $editForm->createView(),
            'delete_form'   => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a User entity.
     *
     * @Route("/{id}/delete", name="users_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $form->bindRequest($this->getRequest());

        if ($form->isValid()) {
            $user = $this->getUser($id);

            if ($this->isCurrentUser($user)) {
                $this->get('session')->setFlash('error', "You can't delete yourself!");
            } else {
                $username = $user->getUsername();
                $this->getUserManager()->deleteUser($user);

                $this->get('session')->setFlash('success', "User '$username' deleted successfully.");
            }
        }

        return $this->redirect($this->generateUrl('users'));
    }

    /**
     * Gets FOSUserBundle's user manager.
     *
     * @return FOS\UserBundle\Model\UserManager
     */
    private function getUserManager()
    {
        return $this->get('fos_user.user_manager');
    }

    /**
     * Gets a user from an id.
     *
     * @param int $id
     * @return SiriuxUserBundle\Entity\User
     * @throws NotFoundHttpException if user is not found.
     */
    private function getUser($id) {
        $user = $this->getUserManager()->findUserBy(array('id' => $id));

        if (!$user) {
            throw $this->createNotFoundException("Unable to find user with id $id.");
        }

        return $user;
    }

    /**
     * Tests weather a certain user is the one currently authenticated.
     *
     * @param SiriuxUserBundle\Entity\User $user
     * @return boolean
     */
    private function isCurrentUser($user)
    {
        $securityToken = $this->get('security.context')->getToken();
        return $user->isUser($securityToken->getUser());
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
