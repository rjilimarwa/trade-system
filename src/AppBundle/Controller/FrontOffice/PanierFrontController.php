<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class PanierController extends Controller
{
    public function menuAction(Request $request)
    {
        // Création de la variable session
        $session = $request->getSession();
        if (!$session->has('panier')) {
            $articles = 0;
        } else {
            $articles = count($session->get('panier'));
        }
        return $this->render('AppBundle/FrontOffice/categories/modulesUsed/panier.html.twig', array('articles' => $articles));
    }

    /**
     * @param $id
     * @return RedirectResponse
     * @Route("/ajouter/{id}", name="ajouter")
     */
    public function ajouterAction($id, Request $request)
    {
        // Création de la variable session
        $session = $request->getSession();

        // On test si la session panier existe
        if (!$session->has('panier')) {
            $session->set('panier', array());
        }

        // On facilite l'acces par une variable intermediaire
        $panier = $session->get('panier');

        // On test si l'article est dans le panier
        if (array_key_exists($id, $panier)) {
            // Si la quantitée n'est pas nulle on change la quantité
            if ($request->query->get('qte') != null) {
                $panier[$id] = $request->query->get('qte');
            }
            $this->get('session')->getFlashBag()->add('success', 'Quantité modifiée avec succès');
        } else {   // si l'article est dans le panier, et que la quantité n'est pas null on change la quantité
            if ($request->query->get('qte') != null) {
                $panier[$id] = $request->query->get('qte');
            } // Sinon on défini la quntité à 1
            else {
                $panier[$id] = 1;
            }
            $this->get('session')->getFlashBag()->add('success', 'Article ajouté avec succès');
        }

        // On remet notre variable intermediaire dans la variable globale SESSION
        $session->set('panier', $panier);

        return $this->redirect($this->generateUrl('panier'));
    }

    /**
     * @param $id
     * @return RedirectResponse
     * @Route("/supprimer/{id}", name="supprimer")
     */
    public function supprimerAction($id, Request $request)
    {
        $session = $request->getSession();
        $panier = $session->get('panier');
        if (array_key_exists($id, $panier)) {
            unset($panier[$id]);
            $session->set('panier', $panier);
            $this->get('session')->getFlashBag()->add('success', 'Article supprimé avec succès');
        }
        return $this->redirect($this->generateUrl('panier'));
    }

    /**
     * @Route("/panier", name="panier")
     */
    public function panierAction()
    {
        $session = $this->getRequest()->getSession();

        if (!$session->has('panier')) {
            $session->set('panier', array());
        }

        $em = $this->getDoctrine()->getManager();
        $produits = $em->getRepository('AppBundle:Produits')->findArray(array_keys($session->get('panier')));
        return $this->render('AppBundle/frontOffice/Panier/index.html.twig', array('titre' => 'Panier',
            'produits' => $produits,
            'panier' => $session->get('panier')));
    }
}
