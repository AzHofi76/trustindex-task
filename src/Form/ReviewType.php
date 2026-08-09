<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the form used to create a review
 *
 *  @author Attila HOFFEREK <azhofi@gmail.com>
 */

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('companyName', TextType::class, [
                'label' => 'Cég neve',
                'attr' => [
                    'placeholder' => 'Pl. Acme Kft.',
                    'class' => 'form-control',
                ],
            ])
            ->add('rating', ChoiceType::class, [
                'label' => 'Értékelés (1-5)',
                'choices' => [
                    '5 - Kiváló' => 5,
                    '4 - Jó' => 4,
                    '3 - Átlagos' => 3,
                    '2 - Gyenge' => 2,
                    '1 - Nagyon rossz' => 1,
                ],
                'placeholder' => 'Válassz értékelést...',
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('authorEmail', EmailType::class, [
                'label' => 'E-mail cím',
                'attr' => [
                    'placeholder' => 'minta@domain.hu',
                    'class' => 'form-control',
                ],
            ])
            ->add('reviewText', TextareaType::class, [
                'label' => 'Vélemény leírása',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Írd le a tapasztalataidat (opcionális)...',
                    'class' => 'form-control',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Értékelés beküldése',
                'attr' => [
                    'class' => 'btn btn-primary mt-3',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}