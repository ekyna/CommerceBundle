<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Notification;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\Notification;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\CommerceBundle\Model\QuoteInterface;
use Ekyna\Bundle\CommerceBundle\Model\Recipient;
use Ekyna\Bundle\CommerceBundle\Model\RecipientList;
use Ekyna\Bundle\SettingBundle\Manager\SettingsManager;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Bundle\UserBundle\Repository\UserRepositoryInterface;
use Ekyna\Bundle\UserBundle\Service\Provider\UserProviderInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * Constructor.
     *
     * @param SettingsManager         $settings
     * @param UserProviderInterface   $userProvider
     * @param UserRepositoryInterface $userRepository
     * @param TranslatorInterface     $translator
     */
    public function __construct(
        SettingsManager $settings,
        UserProviderInterface $userProvider,
        UserRepositoryInterface $userRepository,
        TranslatorInterface $translator
    ) {
        $this->settings = $settings;
        $this->userProvider = $userProvider;
        $this->userRepository = $userRepository;
        $this->translator = $translator;
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

        if ($sale instanceof OrderInterface) {
            $type = 'ekyna_commerce.order.label.singular';
        } elseif ($sale instanceof QuoteInterface) {
            $type = 'ekyna_commerce.quote.label.singular';
        } else {
            throw new InvalidArgumentException("Unsupported sale type.");
        }

        $defaultSubject = $this->translator->trans('ekyna_commerce.notification.build.subject', [
            '{type}' => mb_strtolower($this->translator->trans($type)),
            '{number}' => $sale->getNumber(),
        ]);

        $notification->setSubject($defaultSubject);

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
        $from = new RecipientList();

        /** @var UserInterface[] $administrators */
        $administrators = $this->userRepository->findByRole('ROLE_ADMIN');
        foreach ($administrators as $administrator) {
            $from->add($this->createRecipient($administrator, 'Administrateur'));
        }

        $from->add($this->createWebsiteRecipient());

        if ($sale instanceof OrderInterface/* || $sale instanceof QuoteInterface*/) {
            if ($inCharge = $sale->getInCharge()) {
                $from->add($this->createRecipient($inCharge, 'Responsable'));
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
            $list->add($this->createRecipient($customer, 'Client')); // TODO constant / translation
            if ($parent = $customer->getParent()) {
                $list->add($this->createRecipient($parent, 'Facturation'));
            }
        } else {
            $list->add($this->createRecipient($sale, 'Client'));
        }

        if ($sale instanceof OrderInterface) {
            if (null !== $customer = $sale->getOriginCustomer()) {
                $list->add($this->createRecipient($customer, 'Client d\'origine'));
            }
        }
        if ($sale instanceof OrderInterface || $sale instanceof QuoteInterface) {
            if ($inCharge = $sale->getInCharge()) {
                $list->add($this->createRecipient($inCharge, 'Responsable'));
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
                $copies->add($this->createRecipient($parent, 'Facturation')); // TODO constant / translation
            }
        }

        /** @var UserInterface[] $administrators */
        $administrators = $this->userRepository->findByRole('ROLE_ADMIN');
        foreach ($administrators as $administrator) {
            $copies->add($this->createRecipient($administrator, 'Administrateur'));
        }

        $copies->add($this->createWebsiteRecipient());

        return $copies->all();
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
            return new Recipient($element->getEmail(), $element->getUsername(), $type);
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
