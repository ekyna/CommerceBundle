<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Invoice;

use DateTime;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\ArrayAddressType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Common\Locking\LockChecker;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Invoice\Builder\InvoiceBuilderInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use function Symfony\Component\Translation\t;

/**
 * Class InvoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Invoice
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceType extends AbstractResourceType
{
    public function __construct(
        private readonly InvoiceBuilderInterface       $builder,
        private readonly LockChecker                   $lockChecker,
        private readonly AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', Type\TextType::class, [
                'label'    => t('field.number', [], 'EkynaUi'),
                'required' => false,
                'disabled' => true,
            ])
            ->add('comment', Type\TextareaType::class, [
                'label'    => t('field.comment', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('description', Type\TextareaType::class, [
                'label'    => t('field.description', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
                $form = $event->getForm();
                /** @var InvoiceInterface $invoice */
                $invoice = $event->getData();

                if (null === $sale = $invoice->getSale()) {
                    throw new RuntimeException('The invoice must be associated with a sale at this point.');
                }
                if (!$sale instanceof OrderInterface) {
                    throw new RuntimeException('Not yet supported.');
                }

                $locked = $this->lockChecker->isLocked($invoice)
                    && !$this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN');

                $form->add('createdAt', Type\DateTimeType::class, [
                    'label'      => t('field.date', [], 'EkynaUi'),
                    'required'   => false,
                    'disabled'   => $locked,
                    'empty_data' => (new DateTime())->format('d/m/Y H:i'), // TODO Use the proper format !
                ]);

                if ($invoice->isCredit()) {
                    $form->add('ignoreStock', Type\CheckboxType::class, [
                        'label'    => t('invoice.field.ignore_stock', [], 'EkynaCommerce'),
                        'required' => false,
                        'disabled' => $locked,
                        'help'     => t('invoice.help.ignore_stock', [], 'EkynaCommerce'),
                        'attr'     => [
                            'align_with_widget' => true,
                        ],
                    ]);
                }

                $disabledLines = true;
                if (null === $invoice->getShipment()) {
                    $form->add('items', InvoiceItemsType::class, [
                        'entry_type'    => $options['item_type'],
                        'entry_options' => [
                            'invoice' => $invoice,
                        ],
                        'disabled'      => $locked,
                    ]);

                    $this->builder->build($invoice);

                    $disabledLines = false;
                }

                $form
                    ->add('lines', InvoiceTreeType::class, [
                        'invoice'    => $invoice,
                        'entry_type' => $options['line_type'],
                        'disabled'   => $locked || $disabledLines,
                    ])
                    // TODO Test post_submit event
                    ->add('customInvoiceAddress', ArrayAddressType::class, [
                        'label'    => t('sale.field.invoice_address', [], 'EkynaCommerce'),
                        'required' => false,
                        'disabled' => $locked,
                        'attr'     => [
                            'class' => 'invoice-invoice-address',
                        ],
                    ])
                    ->add('customDeliveryAddress', ArrayAddressType::class, [
                        'label'    => t('sale.field.delivery_address', [], 'EkynaCommerce'),
                        'required' => false,
                        'disabled' => $locked,
                        'attr'     => [
                            'class' => 'invoice-delivery-address',
                        ],
                    ]);
            });
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var InvoiceInterface $invoice */
        $invoice = $form->getData();

        $locked = $this->lockChecker->isLocked($invoice);
        $privileged = $locked && $this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN');

        $view->vars['credit_mode'] = $invoice->isCredit();
        $view->vars['with_availability'] = !$locked;
        $view->vars['privileged'] = $privileged;

        FormUtil::addClass($view, 'invoice');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired([
                'line_type',
                'item_type',
            ])
            ->setAllowedTypes('line_type', 'string')
            ->setAllowedTypes('item_type', 'string');
    }
}
