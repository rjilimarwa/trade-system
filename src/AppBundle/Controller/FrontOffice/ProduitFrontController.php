<?php

namespace AppBundle\Controller\FrontOffice;
use AppBundle\Entity\Produit;
use AppBundle\Entity\Category;
use AppBundle\Form\ProduitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ProduitFrontController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();

        $findProduits = $em->getRepository('AppBundle:Produit')->findBy(array('available' => 1));
        if ($session->has('panier')) {
            $panier = $session->get('panier');
        } else {
            $panier = false;
        }

        $produits = $this->get('knp_paginator')->paginate($findProduits, $request->query->get('page', 1)/*page number*/, 4/*limit per page*/);

        return $this->render('frontOffice/Produits/index.html.twig', array('titre' => 'Produits', 'produits' => $produits, 'panier' => $panier));
    }

    /**
     * @Route("/produit/{id}", name="produit")
     */



}
