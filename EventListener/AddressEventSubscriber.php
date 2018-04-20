<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Component\Commerce\Cart\Event\CartAddressEvents;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Customer\Event\CustomerAddressEvents;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Event\OrderAddressEvents;
use Ekyna\Component\Commerce\Quote\Event\QuoteAddressEvents;
use Ekyna\Component\Commerce\Shipment\Event\RelayPointEvents;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Ivory\GoogleMap\Service\Geocoder\GeocoderService;
use Ivory\GoogleMap\Service\Geocoder\Request\GeocoderAddressRequest;
use Ivory\GoogleMap\Service\Geocoder\Response\GeocoderStatus;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AddressEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddressEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var GeocoderService
     */
    protected $geocoder;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param GeocoderService            $geocoder
     */
    public function __construct(PersistenceHelperInterface $persistenceHelper, GeocoderService $geocoder)
    {
        $this->persistenceHelper = $persistenceHelper;
        $this->geocoder = $geocoder;
    }

    /**
     * Address insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $address = $this->getAddressFromEvent($event);

        // Abort if both longitude and latitude have been set by javascript
        if (!is_null($address->getLongitude()) && !is_null($address->getLatitude())) {
            return;
        }

        if ($this->updateLocation($address)) {
            $this->persistenceHelper->persistAndRecompute($address, false);
        }
    }

    /**
     * Address update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $address = $this->getAddressFromEvent($event);

        // If both longitude and latitude have been previously set
        if (!is_null($address->getLongitude()) && !is_null($address->getLatitude())) {
            // Abort if none of the fields use to geolocalize has changed
            if (!$this->persistenceHelper->isChanged($address, ['street', 'postalCode', 'city', 'country'])) {
                return;
            }
        }

        if ($this->updateLocation($address)) {
            /** @noinspection PhpParamsInspection */
            $this->persistenceHelper->persistAndRecompute($address, false);
        }
    }

    /**
     * Updates the address longitude and latitude.
     *
     * @param AddressInterface $address
     *
     * @return bool Whether the longitude and latitude have been updated.
     */
    protected function updateLocation(AddressInterface $address)
    {
        $data = implode(', ', [
            $address->getStreet(),
            $address->getPostalCode(),
            $address->getCity(),
            $address->getCountry()->getCode(),
        ]);

        $request = new GeocoderAddressRequest($data);

        $response = $this->geocoder->geocode($request);
        if ($response->getStatus() !== GeocoderStatus::OK) {
            return false;
        }

        if (false === $result = current($response->getResults())) {
            return false;
        }

        /** @var \Ivory\GoogleMap\Service\Geocoder\Response\GeocoderResult $result */
        $location = $result->getGeometry()->getLocation();

        $address
            ->setLongitude($location->getLongitude())
            ->setLatitude($location->getLatitude());

        return true;
    }

    /**
     * Returns the address from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return AddressInterface|ResourceInterface
     */
    protected function getAddressFromEvent(ResourceEventInterface $event)
    {
        $address = $event->getResource();

        if (!$address instanceof AddressInterface) {
            throw new InvalidArgumentException("Expected instance of " . AddressInterface::class);
        }

        return $address;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            CustomerAddressEvents::INSERT => ['onInsert', -2048],
            CustomerAddressEvents::UPDATE => ['onUpdate', -2048],
            CartAddressEvents::INSERT     => ['onInsert', -2048],
            CartAddressEvents::UPDATE     => ['onUpdate', -2048],
            QuoteAddressEvents::INSERT    => ['onInsert', -2048],
            QuoteAddressEvents::UPDATE    => ['onUpdate', -2048],
            OrderAddressEvents::INSERT    => ['onInsert', -2048],
            OrderAddressEvents::UPDATE    => ['onUpdate', -2048],
            RelayPointEvents::INSERT      => ['onInsert', -2048],
            RelayPointEvents::UPDATE      => ['onUpdate', -2048],
        ];
    }
}