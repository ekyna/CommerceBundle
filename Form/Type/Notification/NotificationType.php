<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Notification;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\CommerceBundle\Model\InvoiceTypes;
use Ekyna\Bundle\CommerceBundle\Model\Notification;
use Ekyna\Bundle\CommerceBundle\Service\Notification\NotificationBuilder;
use Ekyna\Bundle\CoreBundle\Form\Type\TinymceType;
use Ekyna\Component\Commerce\Common\Model\AttachmentInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Entity\OrderAttachment;
use Ekyna\Component\Commerce\Order\Entity\OrderInvoice;
use Ekyna\Component\Commerce\Order\Entity\OrderShipment;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Quote\Entity\QuoteAttachment;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class NotificationType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotificationType extends AbstractType
{
    /**
     * @var NotificationBuilder
     */
    private $notificationBuilder;

    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * Constructor.
     *
     * @param NotificationBuilder $notificationBuilder
     * @param TranslatorInterface $translator
     */
    public function __construct(NotificationBuilder $notificationBuilder, TranslatorInterface $translator)
    {
        $this->notificationBuilder = $notificationBuilder;
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $options['sale'];

        $froms = $this->notificationBuilder->createFromListFromSale($sale);
        $recipients = $this->notificationBuilder->createRecipientListFromSale($sale);
        $copies = $this->notificationBuilder->createCopyListFromSale($sale);

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

        $builder
            ->add('from', ChoiceType::class, [
                'label'        => 'ekyna_commerce.notification.field.from',
                'choices'      => $froms,
                'choice_label' => 'choiceLabel',
                'choice_value' => 'email',
                'multiple'     => false,
                'required'     => true,
            ])
            ->add(
                $builder
                    ->create('recipients', ChoiceType::class, [
                        'label'        => 'ekyna_commerce.notification.field.recipients',
                        'choices'      => $recipients,
                        'choice_label' => 'choiceLabel',
                        'choice_value' => 'email',
                        'multiple'     => true,
                        'expanded'     => true,
                        'required'     => false,
                    ])
                    ->addModelTransformer($collectionToArrayTransformer)
            )
            ->add('extraRecipients', RecipientsType::class, [
                'label'    => 'ekyna_commerce.notification.field.recipients',
                'required' => false,
            ])
            ->add(
                $builder
                    ->create('copies', ChoiceType::class, [
                        'label'        => 'ekyna_commerce.notification.field.copies',
                        'choices'      => $copies,
                        'choice_label' => 'choiceLabel',
                        'choice_value' => 'email',
                        'multiple'     => true,
                        'expanded'     => true,
                        'required'     => false,
                    ])
                    ->addModelTransformer($collectionToArrayTransformer)
            )
            ->add('extraCopies', RecipientsType::class, [
                'label'    => 'ekyna_commerce.notification.field.copies',
                'required' => false,
            ]);

        //->add('paymentMessage')
        //->add('shipmentMessage')

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
            $builder->add('invoices', EntityType::class, [
                'label'         => 'ekyna_commerce.notification.field.invoices',
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
            ]);
        }

        if ($sale instanceof OrderInterface) {
            $builder->add('shipments', EntityType::class, [
                'label'         => 'ekyna_commerce.notification.field.shipments',
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
                'label'         => 'ekyna_commerce.notification.field.attachments',
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

        //->add('paymentMessage')
        //->add('shipmentMessage')
        $builder
            ->add('subject', TextType::class, [
                'label' => 'ekyna_commerce.notification.field.subject',
            ])
            ->add('customMessage', TinymceType::class, [
                'label'    => 'ekyna_commerce.notification.field.custom_message',
                'theme'    => 'front',
                'required' => false,
            ])
            ->add('includeView', ChoiceType::class, [
                'label'   => 'ekyna_commerce.notification.field.include_view',
                'choices' => [
                    'ekyna_commerce.notification.include_view.none'   => Notification::VIEW_NONE,
                    'ekyna_commerce.notification.include_view.before' => Notification::VIEW_BEFORE,
                    'ekyna_commerce.notification.include_view.after'  => Notification::VIEW_AFTER,
                ],
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'sale'       => null,
                'data_class' => Notification::class,
            ])
            ->setAllowedTypes('sale', [OrderInterface::class, QuoteInterface::class]);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_notify';
    }
}
