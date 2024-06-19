<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Mailer;

use Ekyna\Bundle\AdminBundle\Service\Mailer\AddressHelper as AdminAddressHelper;
use Ekyna\Bundle\SettingBundle\Manager\SettingManagerInterface;
use Symfony\Component\Mime\Address;

use function sprintf;

/**
 * Class AddressHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Mailer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AddressHelper
{
    private const SALES    = 'sales';
    private const BILLING  = 'billing';
    private const PURCHASE = 'purchase';
    private const SUPPORT  = 'support';

    public const ADDRESSES = [
        self::SALES,
        self::BILLING,
        self::PURCHASE,
        self::SUPPORT,
    ];

    /**
     * @var array<string, Address>
     */
    private array $addresses = [];

    public function __construct(
        private readonly AdminAddressHelper      $adminHelper,
        private readonly SettingManagerInterface $setting,
    ) {
    }

    public function getAdminHelper(): AdminAddressHelper
    {
        return $this->adminHelper;
    }

    public function getSalesAddress(): Address
    {
        return $this->getAddress(self::SALES);
    }

    public function getBillingAddress(): Address
    {
        return $this->getAddress(self::BILLING);
    }

    public function getPurchaseAddress(): Address
    {
        return $this->getAddress(self::PURCHASE);
    }

    public function getSupportAddress(): Address
    {
        return $this->getAddress(self::SUPPORT);
    }

    private function getAddress(string $name): Address
    {
        if (isset($this->addresses[$name])) {
            return $this->addresses[$name];
        }

        $parameter = sprintf('commerce.%s_address', $name);

        if (empty($email = $this->setting->getParameter($parameter))) {
            return $this->addresses[$name] = $this->adminHelper->getNotificationSender();
        }

        return $this->addresses[$name] = new Address($email);
    }
}
