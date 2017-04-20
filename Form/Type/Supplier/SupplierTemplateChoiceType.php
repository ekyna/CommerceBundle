<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Locales;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class SupplierTemplateChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierTemplateChoiceType extends AbstractType
{
    private string $templateClass;
    private array $locales;


    public function __construct(string $class, array $locales)
    {
        $this->templateClass = $class;
        $this->locales       = $locales;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locales = [];
        foreach ($this->locales as $locale) {
            $locales[Locales::getName($locale)] = $locale;
        }

        /** @var SupplierOrderInterface $order */
        $order = $options['order'];

        $builder
            ->add('template', EntityType::class, [
                'label'       => t('field.template', [], 'EkynaCommerce'),
                'placeholder' => t('placeholder.template', [], 'EkynaCommerce'),
                'class'       => $this->templateClass,
                'required'    => false,
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

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var SupplierOrderInterface $order */
        $order = $options['order'];

        $view->vars['order_id'] = $order->getId();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('order')
            ->setDefaults([
                'required' => false,
                'mapped'   => false,
            ])
            ->setAllowedTypes('order', SupplierOrderInterface::class);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_supplier_template_choice';
    }
}
