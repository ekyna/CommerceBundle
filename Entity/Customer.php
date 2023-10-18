<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;
use Ekyna\Bundle\CommerceBundle\Model;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Commerce\Customer\Entity\Customer as BaseCustomer;
use Ekyna\Bundle\CmsBundle\Model as Cms;
use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;

/**
 * Class Customer
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Customer extends BaseCustomer implements Model\CustomerInterface
{
    use Cms\TagsSubjectTrait;
    use Model\InChargeSubjectTrait;

    protected ?UserInterface $user = null;
    protected bool $canReadParentOrders = false;

    public function __construct()
    {
        parent::__construct();

        $this->initializeTags();
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): Model\CustomerInterface
    {
        $this->user = $user;

        return $this;
    }

    public function isCanReadParentOrders(): bool
    {
        return $this->canReadParentOrders;
    }

    public function setCanReadParentOrders(bool $canReadParentOrders): Model\CustomerInterface
    {
        $this->canReadParentOrders = $canReadParentOrders;

        return $this;
    }

    public function getDefaultInvoiceAddress(bool $allowParentAddress = false): ?CustomerAddressInterface
    {
        if ($allowParentAddress && $this->hasParent()) {
            if (null !== $address = $this->parent->getDefaultInvoiceAddress($allowParentAddress)) {
                return $address;
            }
        }

        if (null !== $address = $this->findOneAddressBy(Criteria::expr()->eq('invoiceDefault', true))) {
            return $address;
        }

        return null;
    }

    public function getDefaultDeliveryAddress(bool $allowParentAddress = false): ?CustomerAddressInterface
    {
        if ($allowParentAddress && $this->hasParent()) {
            if (null !== $address = $this->parent->getDefaultDeliveryAddress($allowParentAddress)) {
                return $address;
            }
        }

        if (null !== $address = $this->findOneAddressBy(Criteria::expr()->eq('deliveryDefault', true))) {
            return $address;
        }

        return null;
    }

    /**
     * Finds one address by expression.
     */
    private function findOneAddressBy(Expression $expression): ?CustomerAddressInterface
    {
        if (0 < $this->addresses->count()) {
            $criteria = Criteria::create()
                ->where($expression)
                ->setMaxResults(1);

            $matches = $this->addresses->matching($criteria);
            if (1 === $matches->count()) {
                return $matches->first();
            }
        }

        return null;
    }
}
