<?php
/**
 * Created by PhpStorm.
 * User: Etienne
 * Date: 25/10/2016
 * Time: 22:05
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RechercheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('recherche', 'text', array('attr' => array('class' => 'form-control', 'placeholder' => 'Rechercher un produit')), array('required' => false));
    }

    public function getName()
    {
        return parent::getName(); // TODO: Change the autogenerated stub
    }
}
