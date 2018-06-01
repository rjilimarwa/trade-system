<?php

namespace AppBundle\Form;
use AppBundle\Entity\Category;
use AppBundle\Entity\Tva;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name')->add('description')->add('prix')->add('available')->add('image', ImageType::class, array(
            'required'=> is_null($builder->getData()->getImage()),
            'label'=> 'Image')) ->add('tva', EntityType::class, array(
            'class'=> Tva::class,
            'choice_label'=>'name',
            'placeholder'=>'to choose tva' ))->add('category', EntityType::class, array(
            'class'=> Category::class,
            'choice_label'=>'name',
            'placeholder'=>'choisir category'
        ));
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Produit'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_produit';
    }


}
