<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Notify;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierTemplateChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\InvoiceTypes;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Common\Model\Notify;
use Ekyna\Bundle\CommerceBundle\Service\Notify\RecipientHelper;
use Ekyna\Bundle\CoreBundle\Form\Type\TinymceType;
use Ekyna\Component\Commerce\Common\Model\AttachmentInterface;
use Ekyna\Component\Commerce\Common\Model\Recipient;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Entity\OrderAttachment;
use Ekyna\Component\Commerce\Order\Entity\OrderInvoice;
use Ekyna\Component\Commerce\Order\Entity\OrderShipment;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Quote\Entity\QuoteAttachment;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Supplier\Entity\SupplierOrderAttachment;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class NotifyType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Notify
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotifyType extends AbstractType
{
    /**
     * @var RecipientHelper
     */
    private $recipientHelper;

    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * Constructor.
     *
     * @param RecipientHelper     $recipientHelper
     * @param TranslatorInterface $translator
     */
    public function __construct(RecipientHelper $recipientHelper, TranslatorInterface $translator)
    {
        $this->recipientHelper = $recipientHelper;
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $source = $options['source'];

        if ($source instanceof SaleInterface) {
            $froms = $this->recipientHelper->createFromListFromSale($source);
            $recipients = $this->recipientHelper->createRecipientListFromSale($source);
            $copies = $this->recipientHelper->createCopyListFromSale($source);
        } elseif ($source instanceof SupplierOrderInterface) {
            $froms = $this->recipientHelper->createFromListFromSupplierOrder($source);
            $recipients = $this->recipientHelper->createRecipientListFromSupplierOrder($source);
            $copies = $this->recipientHelper->createCopyListFromSupplierOrder($source);
        } else {
            $froms = [];
            $recipients = [];
            $copies = [];
        }

        $collectionToArrayTransformer = new CallbackTransformer(
            function ($value) {
                if ($value instanceof Collection) {
                    return $value->toArray();
                }

                return $value;
            },
            function ($value) {
                return new ArrayCollection((array)$value);
            }
        );

        // Froms
        if (!empty($froms)) {
            $builder->add('from', ChoiceType::class, [
                'label'        => 'ekyna_commerce.notify.field.from',
                'choices'      => $froms,
                'choice_label' => [$this, 'renderChoiceLabel'],
                'choice_value' => 'email',
                'multiple'     => false,
                'required'     => true,
            ]);
        }

        // Recipients
        if (!empty($recipients)) {
            $builder->add(
                $builder
                    ->create('recipients', ChoiceType::class, [
                        'label'        => 'ekyna_commerce.notify.field.recipients',
                        'choices'      => $recipients,
                        'choice_label' => [$this, 'renderChoiceLabel'],
                        'choice_value' => 'email',
                        'multiple'     => true,
                        'expanded'     => true,
                        'required'     => false,
                    ])
                    ->addModelTransformer($collectionToArrayTransformer)
            );
        }
        $builder->add('extraRecipients', RecipientsType::class, [
            'label'    => 'ekyna_commerce.notify.field.recipients',
            'required' => false,
        ]);

        // Copies
        if (!empty($copies)) {
            $builder->add(
                $builder
                    ->create('copies', ChoiceType::class, [
                        'label'        => 'ekyna_commerce.notify.field.copies',
                        'choices'      => $copies,
                        'choice_label' => [$this, 'renderChoiceLabel'],
                        'choice_value' => 'email',
                        'multiple'     => true,
                        'expanded'     => true,
                        'required'     => false,
                    ])
                    ->addModelTransformer($collectionToArrayTransformer)
            );
        }
        $builder->add('extraCopies', RecipientsType::class, [
            'label'    => 'ekyna_commerce.notify.field.copies',
            'required' => false,
        ]);

        // Source specific
        if ($source instanceof SaleInterface) {
            $this->addSaleFields($builder, $source);
        } elseif ($source instanceof SupplierOrderInterface) {
            $this->addSupplierOrderFields($builder, $source);
        }

        $builder
            ->add('subject', TextType::class, [
                'label' => 'ekyna_core.field.subject',
                'attr'  => [
                    'class' => 'notify-subject',
                ],
            ])
            ->add('customMessage', TinymceType::class, [
                'label'    => 'ekyna_commerce.notify.field.custom_message',
                'theme'    => 'front',
                'required' => false,
                'attr'     => [
                    'class' => 'notify-message',
                ],
            ]);
    }

    /**
     * Adds the sale specific fields.
     *
     * @param FormBuilderInterface $builder
     * @param SaleInterface        $sale
     */
    protected function addSaleFields(FormBuilderInterface $builder, SaleInterface $sale)
    {
        // TODO use SaleFactory to get classes
        if ($sale instanceof OrderInterface) {
            $saleProperty = 'order';
            $attachmentClass = OrderAttachment::class;
        } elseif ($sale instanceof QuoteInterface) {
            $saleProperty = 'quote';
            $attachmentClass = QuoteAttachment::class;
        } else {
            throw new InvalidArgumentException("Unsupported sale.");
        }

        if ($sale instanceof OrderInterface) {
            $builder
                ->add('invoices', EntityType::class, [
                    'label'         => 'ekyna_commerce.notify.field.invoices',
                    'class'         => OrderInvoice::class,
                    'query_builder' => function (EntityRepository $repository) use ($saleProperty, $sale) {
                        $qb = $repository->createQueryBuilder('i');

                        return $qb
                            ->andWhere($qb->expr()->eq('i.' . $saleProperty, ':sale'))
                            ->setParameter('sale', $sale);
                    },
                    'choice_label'  => function ($value) {
                        /** @var \Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface $value */
                        return $this->translator->trans(InvoiceTypes::getLabel($value->getType())) . ' ' . $value->getNumber();
                    },
                    'multiple'      => true,
                    'expanded'      => true,
                    'required'      => false,
                ])
                ->add('shipments', EntityType::class, [
                    'label'         => 'ekyna_commerce.notify.field.shipments',
                    'class'         => OrderShipment::class,
                    'query_builder' => function (EntityRepository $repository) use ($saleProperty, $sale) {
                        $qb = $repository->createQueryBuilder('i');

                        return $qb
                            ->andWhere($qb->expr()->eq('i.' . $saleProperty, ':sale'))
                            ->setParameter('sale', $sale);
                    },
                    'choice_label'  => function ($value) {
                        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface $value */
                        $type = 'ekyna_commerce.' . ($value->isReturn() ? 'return' : 'shipment') . '.label.singular';

                        return $this->translator->trans($type) . ' ' . $value->getNumber();
                    },
                    'multiple'      => true,
                    'expanded'      => true,
                    'required'      => false,
                ]);
        }

        $builder
            ->add('attachments', EntityType::class, [
                'label'         => 'ekyna_commerce.attachment.label.plural',
                'class'         => $attachmentClass,
                'query_builder' => function (EntityRepository $repository) use ($saleProperty, $sale) {
                    $qb = $repository->createQueryBuilder('a');

                    return $qb
                        ->andWhere($qb->expr()->eq('a.' . $saleProperty, ':sale'))
                        ->addOrderBy('a.createdAt', 'DESC')
                        ->setParameter('sale', $sale);
                },
                'choice_label'  => function (AttachmentInterface $attachment) {
                    if (!empty($title = $attachment->getTitle())) {
                        return $attachment->getFilename() . ' :  <em>' . $title . '</em>';
                    }

                    return $attachment->getFilename();
                },
                'multiple'      => true,
                'expanded'      => true,
                'required'      => false,
            ]);

        /*->add('paymentMessage', ChoiceType::class, [
            'label'       => 'ekyna_commerce.notify.field.payment_message',
            'choices'     => [
                'ekyna_core.value.no'  => 0,
                'ekyna_core.value.yes' => 1,
            ],
            'expanded'    => true,
            'required'    => true,
            'attr'        => [
                'class'             => 'inline',
                'align_with_widget' => true,
                // TODO 'help_text' => $paymentMessage->getContent(),
            ],
        ])
        ->add('shipmentMessage', ChoiceType::class, [
            'label'       => 'ekyna_commerce.notify.field.shipment_message',
            'choices'     => [
                'ekyna_core.value.no'  => 0,
                'ekyna_core.value.yes' => 1,
            ],
            'expanded'    => true,
            'required'    => true,
            'attr'        => [
                'class'             => 'inline',
                'align_with_widget' => true,
                // TODO 'help_text' => $shipmentMessage->getContent(),
            ],
        ])*/

        $builder
            ->add('includeView', ChoiceType::class, [
                'label'    => 'ekyna_commerce.notify.field.include_view',
                'choices'  => [
                    'ekyna_commerce.notify.include_view.none'   => Notify::VIEW_NONE,
                    'ekyna_commerce.notify.include_view.before' => Notify::VIEW_BEFORE,
                    'ekyna_commerce.notify.include_view.after'  => Notify::VIEW_AFTER,
                ],
                'expanded' => true,
                'required' => true,
                'attr'     => [
                    'class'             => 'inline',
                    'align_with_widget' => true,
                    'help_text'         => 'ekyna_commerce.notify_model.help.include_view',
                ],
            ]);
    }

    /**
     * Adds the supplier order specific fields.
     *
     * @param FormBuilderInterface   $builder
     * @param SupplierOrderInterface $order
     */
    protected function addSupplierOrderFields(FormBuilderInterface $builder, SupplierOrderInterface $order)
    {
        $builder
            ->add('template', SupplierTemplateChoiceType::class, [
                'order' => $order,
            ])
            ->add('attachments', EntityType::class, [
                'label'         => 'ekyna_commerce.attachment.label.plural',
                'class'         => SupplierOrderAttachment::class,
                'query_builder' => function (EntityRepository $repository) use ($order) {
                    $qb = $repository->createQueryBuilder('a');

                    return $qb
                        ->andWhere($qb->expr()->eq('a.supplierOrder', ':order'))
                        ->addOrderBy('a.createdAt', 'DESC')
                        ->setParameter('order', $order);
                },
                'choice_label'  => function (AttachmentInterface $attachment) {
                    if (!empty($title = $attachment->getTitle())) {
                        return $attachment->getFilename() . ' :  <em>' . $title . '</em>';
                    }

                    return $attachment->getFilename();
                },
                'multiple'      => true,
                'expanded'      => true,
                'required'      => false,
            ])
            ->add('includeForm', CheckboxType::class, [
                'label'    => 'ekyna_commerce.notify.field.include_form',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ]);
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $email = null;
        if ($user = $this->recipientHelper->getUserProvider()->getUser()) {
            $email = $user->getEmail();
        }

        FormUtil::addClass($view, 'commerce-notify');

        $view->vars['attr']['data-current-user'] = $email;
    }

    /**
     * Renders the recipient choice label.
     *
     * @param Recipient $recipient
     *
     * @return string
     */
    public function renderChoiceLabel(Recipient $recipient)
    {
        $label = '';

        if (!empty($type = $recipient->getType())) {
            $label = '[' . $this->translator->trans('ekyna_commerce.notify.recipient.' . $type) . '] ';
        }

        if (!empty($name = $recipient->getName())) {
            $label .= sprintf('%s &lt;%s&gt;', $name, $recipient->getEmail());
        } else {
            $label .= $recipient->getEmail();
        }

        return $label;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'source'     => null,
                'data_class' => Notify::class,
            ])
            ->setAllowedTypes('source', [
                OrderInterface::class,
                QuoteInterface::class,
                SupplierOrderInterface::class,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_notify';
    }
}
