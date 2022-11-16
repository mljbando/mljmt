<?php

namespace Customize\Form\Extension;

use Eccube\Form\Type\Admin\SearchCustomerType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchCustomerMailMagazineType extends AbstractTypeExtension
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options = $builder
            ->add('mail_magazine', CheckboxType::class, [
                'label' => 'admin.common.mail_magazine',
                'required' => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return SearchCustomerType::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield SearchCustomerType::class;
    }
}