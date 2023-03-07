<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Geocoder\Geocoder;
use Geocoder\Query\GeocodeQuery;
use Throwable;

/**
 * Class AddressListener
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddressListener
{
    public function __construct(
        private readonly PersistenceHelperInterface $persistenceHelper,
        private readonly Geocoder                   $geocoder
    ) {
    }

    /**
     * Address insert event handler.
     */
    public function onInsert(ResourceEventInterface $event): void
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
     */
    public function onUpdate(ResourceEventInterface $event): void
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
            $this->persistenceHelper->persistAndRecompute($address, false);
        }
    }

    /**
     * Updates the address longitude and latitude.
     *
     * @return bool Whether the longitude and latitude have been updated.
     */
    protected function updateLocation(AddressInterface $address): bool
    {
        $data = implode(', ', [
            $address->getStreet(),
            $address->getPostalCode(),
            $address->getCity(),
            $address->getCountry()->getCode(),
        ]);

        $request = GeocodeQuery::create($data);

        try {
            $result = $this->geocoder->geocodeQuery($request);
        } catch (Throwable) {
            return false;
        }

        if ($result->isEmpty()) {
            return false;
        }

        $coordinates = $result->first()->getCoordinates();

        $address
            ->setLongitude((string)$coordinates->getLongitude())
            ->setLatitude((string)$coordinates->getLatitude());

        return true;
    }

    /**
     * Returns the address from the event.
     */
    protected function getAddressFromEvent(ResourceEventInterface $event): AddressInterface|ResourceInterface
    {
        $address = $event->getResource();

        if (!$address instanceof AddressInterface) {
            throw new UnexpectedTypeException($address, AddressInterface::class);
        }

        return $address;
    }
}
