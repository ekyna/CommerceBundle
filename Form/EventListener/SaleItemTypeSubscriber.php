<?php

namespace Ekyna\Bundle\CommerceBundle\Form\EventListener;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class SaleItemTypeSubscriber
 * @package Ekyna\Bundle\CommerceBundle\Form\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleItemTypeSubscriber implements EventSubscriberInterface
{
    /**
     * @var array
     */
    private $fields;


    /**
     * Constructor.
     *
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * Form pre set data event handler.
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $item = $event->getData();

        foreach ($this->fields as $field) {
            list ($name, $type, $options) = $field;
            if (null !== $item) {
                $options = array_replace($options, $this->getFormOptions($item, $name));
            }
            $form->add($name, $type, $options);
        }
    }

    /**
     * {@inheritdoc}
     */
    private function getFormOptions(SaleItemInterface $item, $property)
    {
        $options = [];

        if ($property === 'netPrice') {
            if (null === $sale = $item->getSale()) {
                throw new LogicException("Item's sale must be set at this point.");
            }
            if (null === $currency = $sale->getCurrency()) {
                throw new LogicException("Supplier's currency must be set at this point.");
            }
            $options['currency'] = $currency->getCode();
        }

        if ($item->hasChildren() && in_array($property, ['netPrice', 'weight', 'taxGroup'])) {
            $options['disabled'] = true;
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    static public function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => ['onPreSetData', 0],
        ];
    }
}
