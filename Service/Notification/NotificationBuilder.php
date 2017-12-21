<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Notification;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\Notification;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\CommerceBundle\Model\QuoteInterface;
use Ekyna\Bundle\CommerceBundle\Model\Recipient;
use Ekyna\Bundle\SettingBundle\Manager\SettingsManager;
use Ekyna\Bundle\UserBundle\Repository\GroupRepository;
use Ekyna\Bundle\UserBundle\Repository\UserRepository;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Bundle\UserBundle\Service\Provider\UserProviderInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class NotificationBuilder
 * @package Ekyna\Bundle\CommerceBundle\Service\Notification
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotificationBuilder
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
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var GroupRepository
     */
    private $groupRepository;


    /**
     * Constructor.
     *
     * @param SettingsManager       $settings
     * @param UserProviderInterface $userProvider
     * @param UserRepository        $userRepository
     * @param GroupRepository       $groupRepository
     */
    public function __construct(
        SettingsManager $settings,
        UserProviderInterface $userProvider,
        UserRepository $userRepository,
        GroupRepository $groupRepository
    ) {
        $this->settings = $settings;
        $this->userProvider = $userProvider;
        $this->userRepository = $userRepository;
        $this->groupRepository = $groupRepository;
    }

    /**
     * Creates the notification from the given sale.
     *
     * @param SaleInterface $sale
     *
     * @return Notification
     */
    public function createNotificationFromSale(SaleInterface $sale)
    {
        $notification = new Notification();

        $from = $this->createWebsiteRecipient();
        if ($sale instanceof OrderInterface || $sale instanceof QuoteInterface) {
            if ($inCharge = $sale->getInCharge()) {
                $from = $this->createRecipient($inCharge, 'Responsable');
            } elseif (null !== $recipient = $this->createCurrentUserRecipient()) {
                $from = $recipient;
            }
        }
        $notification->setFrom($from);

        if ($customer = $sale->getCustomer()) {
            $notification->addRecipient($this->createRecipient($customer, 'Client')); // TODO constant / translation
        } else {
            $notification->addRecipient($this->createRecipient($sale, 'Client'));
        }

        // TODO Attachments regarding to state

        // TODO Payment and shipment messages

        return $notification;
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
        $froms = [$this->createWebsiteRecipient()];

        if ($sale instanceof OrderInterface/* || $sale instanceof QuoteInterface*/) {
            if ($inCharge = $sale->getInCharge()) {
                array_unshift($froms, $this->createRecipient($inCharge, 'Responsable'));
            } elseif (null !== $recipient = $this->createCurrentUserRecipient()) {
                array_unshift($froms, $recipient);
            }
        }

        return $froms;
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
        $recipients = [];

        if ($customer = $sale->getCustomer()) {
            $recipients[] = $this->createRecipient($customer, 'Client'); // TODO constant / translation
            if ($parent = $customer->getParent()) {
                $recipients[] = $this->createRecipient($parent, 'Facturation');
            }
        } else {
            $recipients[] = $this->createRecipient($sale, 'Client');
        }

        if ($sale instanceof OrderInterface) {
            if (null !== $customer = $sale->getOriginCustomer()) {
                $recipients[] = $this->createRecipient($customer, 'Client d\'origine');
            }
        }
        if ($sale instanceof OrderInterface || $sale instanceof QuoteInterface) {
            if ($inCharge = $sale->getInCharge()) {
                $recipients[] = $this->createRecipient($inCharge, 'Responsable');
            } elseif (null !== $recipient = $this->createCurrentUserRecipient()) {
                $recipients[] = $recipient;
            }
        }

        return $recipients;
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
        $copies = [$this->createWebsiteRecipient()];

        if ($customer = $sale->getCustomer()) {
            if ($parent = $customer->getParent()) {
                $copies[] = $this->createRecipient($parent, 'Facturation'); // TODO constant / translation
            }
        }

        /** @var UserInterface[] $administrators */
        $administrators = $this->userRepository->findBy([
            'group' => $this->groupRepository->findOneByRole('ROLE_ADMIN'),
        ]);
        foreach ($administrators as $administrator) {
            $copies[] = $this->createRecipient($administrator, 'Administrateur');
        }

        return $copies;
    }

    /**
     * Returns the general website recipient.
     *
     * @return Recipient
     */
    protected function createWebsiteRecipient()
    {
        return new Recipient(
            $this->settings->getParameter('general.admin_email'),
            $this->settings->getParameter('general.admin_name'),
            'WebSite'
        );
    }

    /**
     * Returns the current user recipient.
     *
     * @return Recipient
     */
    protected function createCurrentUserRecipient()
    {
        if (null !== $user = $this->userProvider->getUser()) {
            return new Recipient(
                $user->getEmail(),
                $user->getUsername(),
                'You'
            );
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
            return new Recipient($element->getEmail(), null, $type);
        }

        if ($element instanceof SaleInterface || $element instanceof CustomerInterface) {
            return new Recipient($element->getEmail(), $element->getFirstName() . ' ' . $element->getLastName(), $type);
        }

        throw new InvalidArgumentException(sprintf(
            'Expected instance of %s, %s or %s',
            SaleInterface::class,
            CustomerInterface::class,
            UserInterface::class
        ));
    }
}
