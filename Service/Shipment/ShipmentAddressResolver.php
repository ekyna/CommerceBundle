<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Shipment;

use Ekyna\Bundle\SettingBundle\Manager\SettingManagerInterface;
use Ekyna\Component\Commerce\Bridge\Symfony\Transformer\ShipmentAddressTransformer;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentAddress;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentAddressResolver as BaseResolver;
use libphonenumber\PhoneNumberUtil;

/**
 * Class ShipmentAddressResolver
 * @package Ekyna\Bundle\CommerceBundle\Service\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentAddressResolver extends BaseResolver
{
    private SettingManagerInterface $settingsManager;

    private ?PhoneNumberUtil $phoneUtil      = null;
    private ?ShipmentAddress $companyAddress = null;

    public function __construct(ShipmentAddressTransformer $transformer, SettingManagerInterface $settingsManager)
    {
        parent::__construct($transformer);

        $this->settingsManager = $settingsManager;
    }

    protected function getCompanyAddress(): AddressInterface
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

        $country = $this->findCountryByCode($siteAddress->getCountry());

        $companyAddress = new ShipmentAddress();
        $companyAddress
            ->setCompany($companyName)
            ->setStreet($siteAddress->getStreet())
            ->setComplement($siteAddress->getSupplement())
            //->setSupplement($siteAddress->getSupplement())
            ->setPostalCode($siteAddress->getPostalCode())
            ->setCity($siteAddress->getCity())
            ->setCountry($country);
        // TODO ->setState($this->findStateByName($siteAddress->getState()));

        if (null === $this->phoneUtil) {
            $this->phoneUtil = PhoneNumberUtil::getInstance();
        }
        if (!empty($phone = $siteAddress->getPhone())) {
            $companyAddress->setPhone($this->phoneUtil->parse($phone, $country->getCode()));
        }
        if (!empty($mobile = $siteAddress->getMobile())) {
            $companyAddress->setMobile($this->phoneUtil->parse($mobile, $country->getCode()));
        }

        return $this->companyAddress = $companyAddress;
    }

    private function findCountryByCode(string $code): CountryInterface
    {
        if (null === $country = $this->getCountryRepository()->findOneByCode($code)) {
            throw new InvalidArgumentException('Unexpected country code.');
        }

        return $country;
    }
}
