<?php

namespace AppBundle\Controller\BackOffice;

use AppBundle\Entity\UtilisateurAdresse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Utilisateuradresse controller.
 *
 */
class UtilisateurAdresseController extends Controller
{
    /**
     * Lists all utilisateurAdresse entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $utilisateurAdresses = $em->getRepository('AppBundle:UtilisateurAdresse')->findAll();

        return $this->render('administration/utilisateuradresse/index.html.twig', array(
            'utilisateurAdresses' => $utilisateurAdresses,
        ));
    }

    /**
     * Creates a new utilisateurAdresse entity.
     *
     */
    public function newAction(Request $request)
    {
        $utilisateurAdresse = new Utilisateuradresse();
        $form = $this->createForm('AppBundle\Form\UtilisateurAdresseType', $utilisateurAdresse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($utilisateurAdresse);
            $em->flush();

            return $this->redirectToRoute('utilisateuradresse_show', array('id' => $utilisateurAdresse->getId()));
        }

        return $this->render('administration/utilisateuradresse/new.html.twig', array(
            'utilisateurAdresse' => $utilisateurAdresse,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a utilisateurAdresse entity.
     *
     */
    public function showAction(UtilisateurAdresse $utilisateurAdresse)
    {
        $deleteForm = $this->createDeleteForm($utilisateurAdresse);

        return $this->render('administration/utilisateuradresse/show.html.twig', array(
            'utilisateurAdresse' => $utilisateurAdresse,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing utilisateurAdresse entity.
     *
     */
    public function editAction(Request $request, UtilisateurAdresse $utilisateurAdresse)
    {
        $deleteForm = $this->createDeleteForm($utilisateurAdresse);
        $editForm = $this->createForm('AppBundle\Form\UtilisateurAdresseType', $utilisateurAdresse);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('utilisateuradresse_edit', array('id' => $utilisateurAdresse->getId()));
        }

        return $this->render('administration/utilisateuradresse/edit.html.twig', array(
            'utilisateurAdresse' => $utilisateurAdresse,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a utilisateurAdresse entity.
     *
     */
    public function deleteAction(Request $request, UtilisateurAdresse $utilisateurAdresse)
    {
        $form = $this->createDeleteForm($utilisateurAdresse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($utilisateurAdresse);
            $em->flush();
        }

        return $this->redirectToRoute('utilisateuradresse_index');
    }

    /**
     * Creates a form to delete a utilisateurAdresse entity.
     *
     * @param UtilisateurAdresse $utilisateurAdresse The utilisateurAdresse entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(UtilisateurAdresse $utilisateurAdresse)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('utilisateuradresse_delete', array('id' => $utilisateurAdresse->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
