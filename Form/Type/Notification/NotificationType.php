<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Notification;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\CommerceBundle\Model\Notification;
use Ekyna\Bundle\CommerceBundle\Service\Notification\NotificationBuilder;
use Ekyna\Bundle\CoreBundle\Form\Type\TinymceType;
use Ekyna\Component\Commerce\Order\Entity\OrderAttachment;
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
     * Constructor.
     *
     * @param NotificationBuilder $notificationBuilder
     */
    public function __construct(NotificationBuilder $notificationBuilder)
    {
        $this->notificationBuilder = $notificationBuilder;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $options['sale'];

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
            ->add(
                $builder
                    ->create('recipients', ChoiceType::class, [
                        'label'        => 'ekyna_commerce.notification.field.recipients',
                        'choices'      => $recipients,
                        'choice_label' => 'choiceLabel',
                        'multiple'     => true,
                        'expanded'     => true,
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
                        'multiple'     => true,
                        'expanded'     => true,
                    ])
                    ->addModelTransformer($collectionToArrayTransformer)
            )
            ->add('extraCopies', RecipientsType::class, [
                'label'    => 'ekyna_commerce.notification.field.copies',
                'required' => false,
            ]);

        //->add('paymentMessage')
        //->add('shipmentMessage')

        $saleProperty = 'order';
        $attachmentClass = OrderAttachment::class;
        if ($sale instanceof QuoteInterface) {
            $saleProperty = 'quote';
            $attachmentClass = QuoteAttachment::class;
        }

        $builder
            ->add('attachments', EntityType::class, [
                'label'         => 'ekyna_commerce.notification.field.attachments',
                'class'         => $attachmentClass,
                'query_builder' => function (EntityRepository $repository) use ($saleProperty, $sale) {
                    $qb = $repository->createQueryBuilder('a');

                    return $qb
                        ->andWhere($qb->expr()->eq('a.' . $saleProperty, ':sale'))
                        ->setParameter('sale', $sale);
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
}
