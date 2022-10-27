<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Ekyna\Bundle\CommerceBundle\Service\SaleItemHelper;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints\SaleItemAvailability;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SaleItemConfigureType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemConfigureType extends AbstractType
{
    private SaleItemHelper $saleItemHelper;

    public function __construct(SaleItemHelper $saleItemHelper)
    {
        $this->saleItemHelper = $saleItemHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
                if (null === $item = $event->getData()) {
                    throw new LogicException('Sale item must be bound to the form at this point.');
                }

                $this->saleItemHelper->initialize($item, null);
                $this->saleItemHelper->buildForm($item, $event->getForm());
            }, 1024)
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
                $this->saleItemHelper->build($event->getData());
            }, 1024);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr']['id'] = $view->vars['id'];
        $view->vars['submit_button'] = $options['submit_button'];
        $view->vars['extended'] = $options['extended'];
        $view->vars['inner_extra_fields'] = [];

        $this->saleItemHelper->buildFormView($form->getData(), $form, $view);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        FormUtil::addClass($view, 'sale-item-configure');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class'    => SaleItemInterface::class,
                'extended'      => true,
                'submit_button' => false,
                'constraints'   => function (Options $options) {
                    if (!$options['admin_mode']) {
                        return [new SaleItemAvailability()];
                    }

                    return [];
                },
            ])
            ->setAllowedTypes('extended', 'bool')
            ->setAllowedTypes('submit_button', 'bool');
    }
}
