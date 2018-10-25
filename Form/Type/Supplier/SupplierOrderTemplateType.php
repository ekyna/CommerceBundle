<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderTemplates;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SupplierOrderTemplateType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderTemplateType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $locales = [];
        foreach (['en', 'fr'] as $locale) {
            $locales[Intl::getLocaleBundle()->getLocaleName($locale)] = $locale;
        }

        $builder
            ->add('template', ChoiceType::class, [
                'label'       => 'ekyna_commerce.supplier_order.template.label',
                'placeholder' => 'ekyna_commerce.supplier_order.template.placeholder',
                'choices'     => SupplierOrderTemplates::getChoices(),
                'select2' => false,
                'attr'        => [
                    'class' => 'template-choice',
                ],
            ])
            ->add('locale', ChoiceType::class, [
                'choices' => $locales,
                'select2' => false,
                'attr'    => [
                    'class' => 'locale-choice',
                ],
            ]);
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var SupplierOrderInterface $order */
        $order = $options['order'];

        $view->vars['order_id'] = $order->getId();
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('order')
            ->setDefault('mapped', false)
            ->setAllowedTypes('order', SupplierOrderInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_supplier_order_template';
    }
}
