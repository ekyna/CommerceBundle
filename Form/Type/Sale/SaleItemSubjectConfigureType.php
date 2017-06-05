<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Ekyna\Bundle\CommerceBundle\Event\SaleItemFormEvent;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Common\Event\SaleItemEvent;
use Ekyna\Component\Commerce\Common\Event\SaleItemEvents;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
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
                    throw new LogicException('Sale item must be bound to the form at this point.');
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
                    new SaleItemEvent($event->getData())
                );
            }, 1024);
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        FormUtil::addClass($view, 'sale-item-subject-configure');
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
