<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Notify;

use Ekyna\Bundle\AdminBundle\Repository\UserRepositoryInterface;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\InChargeSubjectInterface;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\SettingBundle\Manager\SettingManagerInterface;
use Ekyna\Component\Commerce\Common\Model\Recipient;
use Ekyna\Component\Commerce\Common\Model\RecipientList;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerContactInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\User\Model\UserInterface;
use Ekyna\Component\User\Service\UserProviderInterface;

use function array_replace;

/**
 * Class RecipientHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Notify
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RecipientHelper
{
    private SettingManagerInterface $setting;
    private UserProviderInterface   $userProvider;
    private UserRepositoryInterface $userRepository;
    private array                   $config;


    public function __construct(
        SettingManagerInterface $settings,
        UserProviderInterface   $userProvider,
        UserRepositoryInterface $userRepository,
        array                   $config
    ) {
        $this->setting = $settings;
        $this->userProvider = $userProvider;
        $this->userRepository = $userRepository;
        $this->config = array_replace([
            'administrators' => false,
        ], $config);
    }

    public function getUserProvider(): UserProviderInterface
    {
        return $this->userProvider;
    }

    /**
     * Creates the 'from' list from the given sale.
     *
     * @return Recipient[]
     */
    public function createFromListFromSale(SaleInterface $sale): array
    {
        $from = new RecipientList();

        $this->addAdministrators($from);

        $from->add($this->createWebsiteRecipient());

        if ($recipient = $this->createInChargeRecipient($sale)) {
            $from->add($recipient);
        }

        return $from->all();
    }

    /**
     * Creates the recipient list from the given sale.
     *
     * @return Recipient[]
     */
    public function createRecipientListFromSale(SaleInterface $sale): array
    {
        $list = new RecipientList();

        /** @var CustomerInterface $customer */
        if ($customer = $sale->getCustomer()) {
            $list->add($this->createRecipient($customer, Recipient::TYPE_CUSTOMER));
            $this->addCustomerContacts($customer, $list);
        } else {
            $list->add($this->createRecipient($sale, Recipient::TYPE_CUSTOMER));
        }

        if ($sale instanceof OrderInterface && $customer = $sale->getOriginCustomer()) {
            $list->add($this->createRecipient($customer, Recipient::TYPE_SALESMAN));
            $this->addCustomerContacts($customer, $list, false);
        }

        if ($recipient = $this->createInChargeRecipient($sale)) {
            $list->add($recipient);
        }

        return $list->all();
    }

    /**
     * Creates the copy list from the given sale.
     *
     * @param SaleInterface $sale
     *
     * @return Recipient[]
     */
    public function createCopyListFromSale(SaleInterface $sale): array
    {
        $copies = new RecipientList();

        /** @var CustomerInterface $customer */
        if ($customer = $sale->getCustomer()) {
            $this->addCustomerContacts($customer, $copies);
        }

        $this->addAdministrators($copies);

        $copies->add($this->createWebsiteRecipient());

        return $copies->all();
    }

    /**
     * Creates the 'from' list from the given supplier order.
     *
     * @return Recipient[]
     */
    public function createFromListFromSupplierOrder(SupplierOrderInterface $order): array
    {
        $from = new RecipientList();

        $this->addAdministrators($from);

        $from->add($this->createWebsiteRecipient());

        return $from->all();
    }

    /**
     * Creates the recipient list from the given supplier order.
     *
     * @return Recipient[]
     */
    public function createRecipientListFromSupplierOrder(SupplierOrderInterface $order): array
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
     * @return Recipient[]
     */
    public function createCopyListFromSupplierOrder(SupplierOrderInterface $order): array
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
    public function createWebsiteRecipient(): Recipient
    {
        return new Recipient(
            $this->setting->getParameter('general.admin_email'),
            $this->setting->getParameter('general.admin_name'),
            Recipient::TYPE_WEBSITE
        );
    }

    /**
     * Creates the sale's 'in charge' recipient.
     */
    public function createInChargeRecipient(SaleInterface $sale): ?Recipient
    {
        if (!$this->config['administrators']) {
            return null;
        }

        if (!$sale instanceof InChargeSubjectInterface) {
            return null;
        }

        if ($inCharge = $sale->getInCharge()) {
            return $this->createRecipient($inCharge, Recipient::TYPE_IN_CHARGE);
        }

        return $this->createCurrentUserRecipient();
    }

    /**
     * Returns the current user recipient.
     */
    public function createCurrentUserRecipient(): ?Recipient
    {
        if (!$this->config['administrators']) {
            return null;
        }

        if (null !== $user = $this->userProvider->getUser()) {
            return $this->createRecipient($user, Recipient::TYPE_USER);
        }

        return null;
    }

    /**
     * Creates a recipient from the given element (sale or customer).
     *
     * @param UserInterface|SaleInterface|CustomerInterface|CustomerContactInterface|SupplierInterface $element
     */
    public function createRecipient($element, string $type = null): Recipient
    {
        if ($element instanceof UserInterface) {
            $type = $element === $this->userProvider->getUser() ? Recipient::TYPE_USER : $type;

            return new Recipient(
                $element->getEmail(),
                trim($element->getFirstName() . ' ' . $element->getLastName()),
                $type,
                $element
            );
        }

        if (
            $element instanceof SaleInterface
            || $element instanceof CustomerInterface
            || $element instanceof CustomerContactInterface
        ) {
            return new Recipient($element->getEmail(), trim($element->getFirstName() . ' ' . $element->getLastName()), $type);
        }

        if ($element instanceof SupplierInterface) {
            $name = !$element->isIdentityEmpty() ? trim($element->getFirstName() . ' ' . $element->getLastName()) : null;

            return new Recipient($element->getEmail(), $name, $type);
        }

        throw new UnexpectedTypeException($element, [
            UserInterface::class,
            SaleInterface::class,
            CustomerInterface::class,
            CustomerContactInterface::class,
            SupplierInterface::class,
        ]);
    }

    /**
     * Adds the customer's contacts to the given recipient list.
     */
    private function addCustomerContacts(CustomerInterface $customer, RecipientList $list, bool $parent = true): void
    {
        foreach ($customer->getContacts() as $contact) {
            $list->add($this->createRecipient($contact, Recipient::TYPE_CONTACT));
        }

        if (!$parent) {
            return;
        }

        /** @var CustomerInterface $customer */
        if ($customer = $customer->getParent()) {
            $list->add($this->createRecipient($customer, Recipient::TYPE_ACCOUNTABLE));
            foreach ($customer->getContacts() as $contact) {
                $list->add($this->createRecipient($contact, Recipient::TYPE_CONTACT));
            }
        }
    }

    /**
     * Adds the administrators to the list.
     */
    private function addAdministrators(RecipientList $list): void
    {
        if (!$this->config['administrators']) {
            return;
        }

        $administrators = $this->userRepository->findAllActive();
        foreach ($administrators as $administrator) {
            $list->add($this->createRecipient($administrator, Recipient::TYPE_ADMINISTRATOR));
        }
    }
}
