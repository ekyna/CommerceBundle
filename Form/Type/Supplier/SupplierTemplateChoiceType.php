<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SupplierTemplateChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierTemplateChoiceType extends AbstractType
{
    /**
     * @var string
     */
    private $templateClass;

    /**
     * @var string []
     */
    private $locales;


    /**
     * Constructor.
     *
     * @param string $class
     * @param array  $locales
     */
    public function __construct(string $class, array $locales)
    {
        $this->templateClass = $class;
        $this->locales = $locales;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $locales = [];
        foreach ($this->locales as $locale) {
            $locales[Intl::getLocaleBundle()->getLocaleName($locale)] = $locale;
        }

        /** @var SupplierOrderInterface $order */
        $order = $options['order'];

        $builder
            ->add('template', EntityType::class, [
                'label'       => 'ekyna_commerce.supplier_order.field.template',
                'placeholder' => 'ekyna_commerce.supplier_order.placeholder.template',
                'class'       => $this->templateClass,
                'select2'     => false,
                'attr'        => [
                    'class' => 'template-choice',
                ],
            ])
            ->add('locale', ChoiceType::class, [
                'choices'           => $locales,
                'preferred_choices' => [$order->getLocale()],
                'select2'           => false,
                'attr'              => [
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
        return 'ekyna_commerce_supplier_template_choice';
    }
}
