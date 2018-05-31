<?php

namespace AppBundle\Controller\FrontOffice;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CategoriesController extends Controller
{
    /**liste tous les categories*/
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('AppBundle:Categories')->findAll();

        return $this->render('AppBundle/frontOffice/categories/index.html.twig', array('categories' => $categories));
    }


    /**
     * @Route("/categorie/{id}", name="categorie")
     */
    /**lister  tous les produits a ttravers l'id categories*/
    public function categoriesAction($id, Request $request)
    {
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();

        $findProduits = $em->getRepository('AppBundle:Produits')->byCategorie($id);

        if (!$findProduits) {
            throw $this->createNotFoundException('La page n\'existe pas');
        }

        if ($session->has('panier')) {
            $panier = $session->get('panier');
        } else {
            $panier = false;
        }

        $produits = $this->get('knp_paginator')->paginate($findProduits, $request->query->get('page', 1)/*page number*/, 4/*limit per page*/);

        return $this->render('AppBundle/frontOffice/Produits/index.html.twig', array('titre' => 'Produits', 'produits' => $produits, 'panier' => $panier));
    }
}
