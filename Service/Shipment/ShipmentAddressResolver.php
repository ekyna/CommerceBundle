<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Shipment;

use Ekyna\Bundle\SettingBundle\Manager\SettingsManagerInterface;
use Ekyna\Component\Commerce\Bridge\Symfony\Transformer\ShipmentAddressTransformer;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentAddress;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentAddressResolver as BaseResolver;

/**
 * Class ShipmentAddressResolver
 * @package Ekyna\Bundle\CommerceBundle\Service\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentAddressResolver extends BaseResolver
{
    /**
     * @var ShipmentAddress
     */
    private $companyAddress;

    /**
     * @var SettingsManagerInterface
     */
    private $settingsManager;

    /**
     * @var CountryRepositoryInterface
     */
    private $countryRepository;


    /**
     * Constructor.
     *
     * @param ShipmentAddressTransformer $transformer
     * @param SettingsManagerInterface $settingsManager
     * @param CountryRepositoryInterface $countryRepository
     */
    public function __construct(
        ShipmentAddressTransformer $transformer,
        SettingsManagerInterface $settingsManager,
        CountryRepositoryInterface $countryRepository
    ) {
        parent::__construct($transformer);

        $this->settingsManager = $settingsManager;
        $this->countryRepository = $countryRepository;
    }

    /**
     * @inheritDoc
     */
    protected function getCompanyAddress()
    {
        if (null !== $this->companyAddress) {
            return $this->companyAddress;
        }

        $companyName = $this->settingsManager->getParameter('general.site_name');
        /** @var \Ekyna\Bundle\AdminBundle\Model\SiteAddress $siteAddress */
        $siteAddress = $this->settingsManager->getParameter('general.site_address');

        if (empty($companyName) || empty($siteAddress)) {
            throw new LogicException("Site name and site address parameters must be set.");
        }

        $companyAddress = new ShipmentAddress();
        $companyAddress
            ->setCompany($companyName)
            ->setStreet($siteAddress->getStreet())
            ->setSupplement($siteAddress->getSupplement())
            ->setPostalCode($siteAddress->getPostalCode())
            ->setCity($siteAddress->getCity())
            ->setCountry($this->findCountryByCode($siteAddress->getCountry()))
            // TODO ->setState($this->findStateByName($siteAddress->getState()))
            ->setPhone($siteAddress->getPhone())
            ->setMobile($siteAddress->getMobile());

        return $this->companyAddress = $companyAddress;
    }

    /**
     * Finds the country by its code.
     *
     * @param string $code
     *
     * @return \Ekyna\Component\Commerce\Common\Model\CountryInterface
     */
    private function findCountryByCode($code)
    {
        /** @var \Ekyna\Component\Commerce\Common\Model\CountryInterface $country */
        if (null === $country = $this->countryRepository->findOneBy(['code' => $code])) {
            throw new InvalidArgumentException("Unexpected country code.");
        }

        return $country;
    }
}
