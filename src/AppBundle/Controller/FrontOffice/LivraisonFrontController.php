<?php
/**
 * Created by PhpStorm.
 * User: Etienne
 * Date: 16/10/2016
 * Time: 11:10
 */

namespace AppBundle\Controller\FrontOffice;
use AppBundle\Entity\Utilisateurs;
use AppBundle\Entity\UtilisateursAdresses;
use AppBundle\Form\UtilisateursAdressesType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class LivraisonFrontController extends Controller
{
    /**
     * @Route("/livraison", name="livraison")
     */
    public function livraisonAction(Request $request)
    {
        $utilisateurs = $this->getUser();
        $entity = new UtilisateursAdresses();
        $form = $this->createForm(new UtilisateursAdressesType(), $entity);

        if ($this->get('request')->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $entity->setUtilisateurs($utilisateurs);
                $em->persist($entity);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'Votre adresse a bien été ajoutée');

                return $this->redirect($this->generateUrl('livraison'));
            }
        }

        return $this->render('frontOffice/Livraison/index.html.twig', array('titre' => 'Livraison', 'utilisateur' => $utilisateurs, 'form' => $form->createView()));
    }

    /**
     * @param $id
     * @return string
     * @Route("livraisonAdresseSuppression/{id}", name="livraisonAdresseSuppression")
     */
    public function adresseSuppressionAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:UtilisateurAdresse')->find($id);

        if ($this->getUser() != $entity->getUtilisateur() || !$entity) {
            return $this->generateUrl('livraison');
        }
        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', 'Votre adresse a bien été supprimée');
        return $this->redirect($this->generateUrl('livraison'));
    }

    public function setLivraisonOnSession(Request $request)
    {
        $session = $request->getSession();

        if (!$session->has('adresse')) $session->set('adresse', array());
        $adresse = $session->get('adresse');

        if ($request->request->get('livraison') != null && $request->request->get('facturation')) {
            $adresse['livraison'] = $request->request->get('livraison');
            $adresse['facturation'] = $request->request->get('facturation');
        } else {
            return $this->redirect($this->generateUrl('validation'));
        }
        $session->set('adresse', $adresse);
        return $this->redirect($this->generateUrl('validation'));
    }

    /**
     * @Route("/validation", name="validation")
     */
    public function validationAction(Request $request)
    {
        if ($this->get('request')->getMethod() == 'POST') {
            $this->setLivraisonOnSession($request);
        }

        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $adresse = $session->get('adresse');

        $produits = $em->getRepository('AppBundle:Produits')->findArray(array_keys($session->get('panier')));
        $livraison = $em->getRepository('AppBundle:UtilisateursAdresses')->find($adresse['livraison']);
        $facturation = $em->getRepository('AppBundle:UtilisateursAdresses')->find($adresse['facturation']);

        return $this->render('frontOffice/Livraison/validation.html.twig', array('titre' => 'Validation',
            'produits' => $produits,
            'livraison' => $livraison,
            'facturation' => $facturation,
            'panier' => $session->get('panier')));
    }

}