<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Behat\Transliterator\Transliterator;
use Ekyna\Bundle\CommerceBundle\Model\PaymentMethodInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Payment\Event\PaymentMethodEvents;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PaymentMethodEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethodEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     */
    public function __construct(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
    }

    /**
     * @inheritdoc
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $method = $event->getResource();

        if (!$method instanceof PaymentMethodInterface) {
            throw new InvalidArgumentException('Expected instance of PaymentMethodInterface');
        }

        if (0 == strlen($method->getGatewayName())) {
            $method->setGatewayName(sprintf(
                '%s-%s',
                Transliterator::transliterate($method->getName()),
                uniqid()
            ));

            // TODO check uniqueness

            $this->persistenceHelper->persistAndRecompute($method);
        }
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            PaymentMethodEvents::INSERT => ['onInsert', 0],
        ];
    }
}
