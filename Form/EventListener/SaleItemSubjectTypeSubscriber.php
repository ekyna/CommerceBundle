<?php

namespace Ekyna\Bundle\CommerceBundle\Form\EventListener;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class SaleItemSubjectTypeSubscriber
 * @package Ekyna\Bundle\CommerceBundle\Form\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemSubjectTypeSubscriber implements EventSubscriberInterface
{
    /**
     * @var SubjectProviderRegistryInterface
     */
    private $providerRegistry;


    /**
     * Constructor.
     *
     * @param SubjectProviderRegistryInterface $providerRegistry
     */
    public function __construct(
        SubjectProviderRegistryInterface $providerRegistry
    ) {
        $this->providerRegistry = $providerRegistry;
    }

    /**
     * Form pre set data event handler.
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
        $item = $event->getData();
        $form = $event->getForm();

        $provider = $this->getProvider($item);
        $provider->getItemBuilder()->initializeItem($item);
        $provider->getFormBuilder()->buildItemForm($form, $item);
    }

    /**
     * Form post submit event handler.
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
        $item = $event->getData();

        $provider = $this->getProvider($item);
        $provider->getItemBuilder()->buildItem($item);
    }

    /**
     * Returns the subject provider.
     *
     * @param SaleItemInterface $item
     *
     * @return \Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface|null
     */
    private function getProvider(SaleItemInterface $item)
    {
        if (null === $provider = $this->providerRegistry->getProvider($item)) {
            throw new InvalidArgumentException('Unable to determine subject provider.');
        }

        return $provider;
    }

    /**
     * {@inheritdoc}
     */
    static public function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA  => ['onPreSetData', 1024],
            FormEvents::POST_SUBMIT   => ['onPostSubmit', 1024],
        ];
    }
}
