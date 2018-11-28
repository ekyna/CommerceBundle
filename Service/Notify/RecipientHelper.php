<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Notify;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\CommerceBundle\Model\QuoteInterface;
use Ekyna\Bundle\SettingBundle\Manager\SettingsManager;
use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\AdminBundle\Repository\UserRepositoryInterface;
use Ekyna\Bundle\AdminBundle\Service\Security\UserProviderInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Common\Model\Recipient;
use Ekyna\Component\Commerce\Common\Model\RecipientList;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;

/**
 * Class RecipientHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Notify
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RecipientHelper
{
    /**
     * @var SettingsManager
     */
    private $settings;

    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;


    /**
     * Constructor.
     *
     * @param SettingsManager         $settings
     * @param UserProviderInterface   $userProvider
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        SettingsManager $settings,
        UserProviderInterface $userProvider,
        UserRepositoryInterface $userRepository
    ) {
        $this->settings = $settings;
        $this->userProvider = $userProvider;
        $this->userRepository = $userRepository;
    }

    /**
     * Creates the 'from' list from the given sale.
     *
     * @param SaleInterface $sale
     *
     * @return Recipient[]
     */
    public function createFromListFromSale(SaleInterface $sale)
    {
        $from = new RecipientList();

        $this->addAdministrators($from);

        $from->add($this->createWebsiteRecipient());

        if ($sale instanceof OrderInterface/* || $sale instanceof QuoteInterface*/) {
            if ($inCharge = $sale->getInCharge()) {
                $from->add($this->createRecipient($inCharge, Recipient::TYPE_IN_CHARGE));
            } elseif (null !== $recipient = $this->createCurrentUserRecipient()) {
                $from->add($recipient);
            }
        }

        return $from->all();
    }

    /**
     * Creates the recipient list from the given sale.
     *
     * @param SaleInterface $sale
     *
     * @return Recipient[]
     */
    public function createRecipientListFromSale(SaleInterface $sale)
    {
        $list = new RecipientList();

        if ($customer = $sale->getCustomer()) {
            $list->add($this->createRecipient($customer, Recipient::TYPE_CUSTOMER));
            if ($parent = $customer->getParent()) {
                $list->add($this->createRecipient($parent, Recipient::TYPE_ACCOUNTABLE));
            }
        } else {
            $list->add($this->createRecipient($sale, Recipient::TYPE_CUSTOMER));
        }

        if ($sale instanceof OrderInterface) {
            if (null !== $customer = $sale->getOriginCustomer()) {
                $list->add($this->createRecipient($customer, Recipient::TYPE_SALESMAN));
            }
        }
        if ($sale instanceof OrderInterface || $sale instanceof QuoteInterface) {
            if ($inCharge = $sale->getInCharge()) {
                $list->add($this->createRecipient($inCharge, Recipient::TYPE_IN_CHARGE));
            } elseif (null !== $recipient = $this->createCurrentUserRecipient()) {
                $list->add($recipient);
            }
        }

        return $list->all();
    }

    /**
     * Creates the copy list from the given sale.
     *
     * @param SaleInterface $sale
     *
     * @return array
     */
    public function createCopyListFromSale(SaleInterface $sale)
    {
        $copies = new RecipientList();

        if ($customer = $sale->getCustomer()) {
            if ($parent = $customer->getParent()) {
                $copies->add($this->createRecipient($parent, Recipient::TYPE_ACCOUNTABLE));
            }
        }

        $this->addAdministrators($copies);

        $copies->add($this->createWebsiteRecipient());

        return $copies->all();
    }

    /**
     * Creates the 'from' list from the given supplier order.
     *
     * @param SupplierOrderInterface $order
     *
     * @return Recipient[]
     */
    public function createFromListFromSupplierOrder(SupplierOrderInterface $order)
    {
        $from = new RecipientList();

        $this->addAdministrators($from);

        $from->add($this->createWebsiteRecipient());

        return $from->all();
    }

    /**
     * Creates the recipient list from the given supplier order.
     *
     * @param SupplierOrderInterface $order
     *
     * @return Recipient[]
     */
    public function createRecipientListFromSupplierOrder(SupplierOrderInterface $order)
    {
        $list = new RecipientList();

        if ($supplier = $order->getSupplier()) {
            $list->add($this->createRecipient($supplier, Recipient::TYPE_SUPPLIER));
        }

        return $list->all();
    }

    /**
     * Creates the copy list from the given supplier order.
     *
     * @param SupplierOrderInterface $order
     *
     * @return array
     */
    public function createCopyListFromSupplierOrder(SupplierOrderInterface $order)
    {
        $copies = new RecipientList();

        $this->addAdministrators($copies);

        $copies->add($this->createWebsiteRecipient());

        return $copies->all();
    }

    /**
     * Returns the general website recipient.
     *
     * @return Recipient
     */
    public function createWebsiteRecipient()
    {
        return new Recipient(
            $this->settings->getParameter('general.admin_email'),
            $this->settings->getParameter('general.admin_name'),
            Recipient::TYPE_WEBSITE
        );
    }

    /**
     * Returns the current user recipient.
     *
     * @return Recipient
     */
    public function createCurrentUserRecipient()
    {
        if (null !== $user = $this->userProvider->getUser()) {
            return $this->createRecipient($user, Recipient::TYPE_USER);
        }

        return null;
    }

    /**
     * Creates a recipient from the given element (sale or customer).
     *
     * @param mixed  $element
     * @param string $type
     *
     * @return Recipient
     */
    public function createRecipient($element, $type = null)
    {
        if ($element instanceof UserInterface) {
            $type = $element === $this->userProvider->getUser() ? Recipient::TYPE_USER : $type;
            return new Recipient($element->getEmail(), $element->getFirstName() . ' ' . $element->getLastName(), $type, $element);
        }

        if ($element instanceof SaleInterface || $element instanceof CustomerInterface) {
            return new Recipient($element->getEmail(), $element->getFirstName() . ' ' . $element->getLastName(), $type);
        }

        if ($element instanceof SupplierInterface) {
            $name = !$element->isIdentityEmpty() ? $element->getFirstName() . ' ' . $element->getLastName() : null;

            return new Recipient($element->getEmail(), $name, $type);
        }

        throw new InvalidArgumentException(sprintf(
            'Expected instance of %s, %s or %s',
            SaleInterface::class,
            CustomerInterface::class,
            UserInterface::class
        ));
    }

    /**
     * Adds the administrators to the list.
     *
     * @param RecipientList $list
     */
    private function addAdministrators(RecipientList $list)
    {
        $administrators = $this->userRepository->findAllActive();
        foreach ($administrators as $administrator) {
            $list->add($this->createRecipient($administrator, Recipient::TYPE_ADMINISTRATOR));
        }
    }
}
