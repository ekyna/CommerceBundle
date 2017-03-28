<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Ekyna\Bundle\CommerceBundle\Event\SaleItemFormEvent;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
use Ekyna\Component\Commerce\Common\Event\SaleItemEvent;
use Ekyna\Component\Commerce\Common\Event\SaleItemEvents;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SaleItemSubjectConfigureType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemSubjectConfigureType extends AbstractType
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;


    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                if (null === $item = $event->getData()) {
                    // TODO throw exception ?

                    return;
                }

                // Initialize the item
                $this->eventDispatcher->dispatch(
                    SaleItemEvents::INITIALIZE,
                    new SaleItemEvent($item)
                );

                // Build the form
                $this->eventDispatcher->dispatch(
                    SaleItemFormEvent::BUILD_FORM,
                    new SaleItemFormEvent($item, $event->getForm())
                );
            }, 1024)
            ->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
                // Build the item
                $this->eventDispatcher->dispatch(
                    SaleItemEvents::BUILD,
                    new SaleItemEvent($event->getData(), [
                        ItemBuilder::REMOVE_MISS_MATCH => true,
                    ])
                );
            }, 1024);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SaleItemInterface::class,
        ]);
    }
}
