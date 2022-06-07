<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Notify;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierTemplateChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\DocumentTypes;
use Ekyna\Bundle\CommerceBundle\Service\Notify\RecipientHelper;
use Ekyna\Bundle\UiBundle\Form\Type\TinymceType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Common\Model\AttachmentInterface;
use Ekyna\Component\Commerce\Common\Model\Notify;
use Ekyna\Component\Commerce\Common\Model\Recipient;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Order\Entity\OrderAttachment;
use Ekyna\Component\Commerce\Order\Entity\OrderInvoice;
use Ekyna\Component\Commerce\Order\Entity\OrderShipment;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Quote\Entity\QuoteAttachment;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\Translation\t;

/**
 * Class NotifyType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Notify
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotifyType extends AbstractType
{
    private RecipientHelper               $recipientHelper;
    private TranslatorInterface           $translator;
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(
        RecipientHelper               $recipientHelper,
        TranslatorInterface           $translator,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->recipientHelper = $recipientHelper;
        $this->translator = $translator;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $source = $options['source'];

        // TODO Always use logged-in user as sender.
        $superAdmin = $this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN');

        $senders = [
            $this->recipientHelper->createWebsiteRecipient(),
        ];
        if (null !== $user = $this->recipientHelper->createCurrentUserRecipient()) {
            $senders[] = $user;
        }

        if ($source instanceof SaleInterface) {
            if ($superAdmin) {
                $senders = $this->recipientHelper->createFromListFromSale($source);
            }
            $recipients = $this->recipientHelper->createRecipientListFromSale($source);
            $copies = $this->recipientHelper->createCopyListFromSale($source);
        } elseif ($source instanceof SupplierOrderInterface) {
            if ($superAdmin) {
                $senders = $this->recipientHelper->createFromListFromSupplierOrder($source);
            }
            $recipients = $this->recipientHelper->createRecipientListFromSupplierOrder($source);
            $copies = $this->recipientHelper->createCopyListFromSupplierOrder($source);
        } else {
            $senders = [];
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

        // Senders
        if (!empty($senders)) {
            $builder->add('from', ChoiceType::class, [
                'label'                     => t('notify.field.from', [], 'EkynaCommerce'),
                'choices'                   => $senders,
                'choice_label'              => [$this, 'renderChoiceLabel'],
                'choice_translation_domain' => false,
                'choice_value'              => 'email',
                'multiple'                  => false,
                'required'                  => true,
                'disabled'                  => !$superAdmin,
            ]);
        }

        // Recipients
        if (!empty($recipients)) {
            $builder->add(
                $builder
                    ->create('recipients', ChoiceType::class, [
                        'label'                     => t('notify.field.recipients', [], 'EkynaCommerce'),
                        'choices'                   => $recipients,
                        'choice_label'              => [$this, 'renderChoiceLabel'],
                        'choice_translation_domain' => false,
                        'choice_value'              => 'email',
                        'multiple'                  => true,
                        'expanded'                  => true,
                        'required'                  => false,
                    ])
                    ->addModelTransformer($collectionToArrayTransformer)
            );
        }
        $builder->add('extraRecipients', RecipientsType::class, [
            'label'    => t('notify.field.recipients', [], 'EkynaCommerce'),
            'required' => false,
        ]);

        // Copies
        if (!empty($copies)) {
            $builder->add(
                $builder
                    ->create('copies', ChoiceType::class, [
                        'label'                     => t('notify.field.copies', [], 'EkynaCommerce'),
                        'choices'                   => $copies,
                        'choice_label'              => [$this, 'renderChoiceLabel'],
                        'choice_translation_domain' => false,
                        'choice_value'              => 'email',
                        'multiple'                  => true,
                        'expanded'                  => true,
                        'required'                  => false,
                    ])
                    ->addModelTransformer($collectionToArrayTransformer)
            );
        }
        $builder->add('extraCopies', RecipientsType::class, [
            'label'    => t('notify.field.copies', [], 'EkynaCommerce'),
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
                'label' => t('field.subject', [], 'EkynaUi'),
                'attr'  => [
                    'class' => 'notify-subject',
                ],
            ])
            ->add('customMessage', TinymceType::class, [
                'label'    => t('notify.field.custom_message', [], 'EkynaCommerce'),
                'theme'    => 'front',
                'required' => false,
                'attr'     => [
                    'class' => 'notify-message',
                ],
            ]);
    }

    /**
     * Adds the sale specific fields.
     */
    protected function addSaleFields(FormBuilderInterface $builder, SaleInterface $sale): void
    {
        // TODO use SaleFactory to get classes
        if ($sale instanceof OrderInterface) {
            $saleProperty = 'order';
            $attachmentClass = OrderAttachment::class;
        } elseif ($sale instanceof QuoteInterface) {
            $saleProperty = 'quote';
            $attachmentClass = QuoteAttachment::class;
        } else {
            throw new InvalidArgumentException('Unsupported sale.');
        }

        if ($sale instanceof OrderInterface) {
            $builder
                ->add('invoices', EntityType::class, [
                    'label'         => t('notify.field.invoices', [], 'EkynaCommerce'),
                    'class'         => OrderInvoice::class,
                    'query_builder' => function (EntityRepository $repository) use ($saleProperty, $sale) {
                        $qb = $repository->createQueryBuilder('i');

                        return $qb
                            ->andWhere($qb->expr()->eq('i.' . $saleProperty, ':sale'))
                            ->setParameter('sale', $sale);
                    },
                    'choice_label'  => function ($value) {
                        /** @var InvoiceInterface $value */
                        return DocumentTypes::getLabel($value->getType())->trans($this->translator)
                            . ' ' . $value->getNumber();
                    },
                    'multiple'      => true,
                    'expanded'      => true,
                    'required'      => false,
                ])
                ->add('shipments', EntityType::class, [
                    'label'         => t('notify.field.shipments', [], 'EkynaCommerce'),
                    'class'         => OrderShipment::class,
                    'query_builder' => function (EntityRepository $repository) use ($saleProperty, $sale) {
                        $qb = $repository->createQueryBuilder('i');

                        return $qb
                            ->andWhere($qb->expr()->eq('i.' . $saleProperty, ':sale'))
                            ->setParameter('sale', $sale);
                    },
                    'choice_label'  => function ($value) {
                        /** @var ShipmentInterface $value */
                        $type = ($value->isReturn() ? 'return' : 'shipment') . '.label.singular';

                        return $this->translator->trans($type, [], 'EkynaCommerce') . ' ' . $value->getNumber();
                    },
                    'multiple'      => true,
                    'expanded'      => true,
                    'required'      => false,
                ]);
        }

        $builder
            ->add('attachments', EntityType::class, [
                'label'         => t('attachment.label.plural', [], 'EkynaCommerce'),
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
            'label'       => t('notify.field.payment_message', [], 'EkynaCommerce'),
            'choices'     => [
                'value.no'  => 0,
                'value.yes' => 1,
            ],
            'choice_translation_domain' => 'EkynaUi',
            'expanded'    => true,
            'required'    => true,
            'attr'        => [
                'class'             => 'inline',
                'align_with_widget' => true,
                // TODO 'help_text' => $paymentMessage->getContent(),
            ],
        ])
        ->add('shipmentMessage', ChoiceType::class, [
            'label'       => t('notify.field.shipment_message', [], 'EkynaCommerce'),
            'choices'     => [
                'value.no'  => 0,
                'value.yes' => 1,
            ],
            'choice_translation_domain' => 'EkynaUi',
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
                'label'                     => t('notify.field.include_view', [], 'EkynaCommerce'),
                'choices'                   => [
                    'notify.include_view.none'   => Notify::VIEW_NONE,
                    'notify.include_view.before' => Notify::VIEW_BEFORE,
                    'notify.include_view.after'  => Notify::VIEW_AFTER,
                ],
                'choice_translation_domain' => 'EkynaCommerce',
                'expanded'                  => true,
                'required'                  => true,
                'attr'                      => [
                    'class'             => 'inline',
                    'align_with_widget' => true,
                    'help_text'         => t('notify_model.help.include_view', [], 'EkynaCommerce'),
                ],
            ])
            ->add('model', NotifyModelChoiceType::class, [
                'sale' => $sale,
            ]);
    }

    /**
     * Adds the supplier order specific fields.
     */
    protected function addSupplierOrderFields(FormBuilderInterface $builder, SupplierOrderInterface $order): void
    {
        $builder
            ->add('template', SupplierTemplateChoiceType::class, [
                'order' => $order,
            ])
            ->add('attachments', EntityType::class, [
                'label'         => t('attachment.label.plural', [], 'EkynaCommerce'),
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
                'label'    => t('notify.field.include_form', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
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
     */
    public function renderChoiceLabel(Recipient $recipient): string
    {
        $label = '';

        if (!empty($type = $recipient->getType())) {
            $label = '[' . $this->translator->trans('notify.recipient.' . $type, [], 'EkynaCommerce') . '] ';
        }

        if (!empty($name = $recipient->getName())) {
            $label .= sprintf('%s &lt;%s&gt;', $name, $recipient->getEmail());
        } else {
            $label .= $recipient->getEmail();
        }

        return $label;
    }

    public function configureOptions(OptionsResolver $resolver): void
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

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_notify';
    }
}
