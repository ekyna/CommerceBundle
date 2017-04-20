<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Model\AccountingTypes;
use Ekyna\Component\Commerce\Accounting\Event\AccountingEvents;
use Ekyna\Component\Commerce\Accounting\EventListener\AccountingListener;
use Ekyna\Component\Commerce\Accounting\Model\AccountingInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AccountingEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AccountingEventSubscriber extends AccountingListener implements EventSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    protected function buildName(AccountingInterface $accounting)
    {
        $parts = [
            AccountingTypes::getLabel($accounting->getType())->trans($this->translator),
        ];

        if (null !== $rule = $accounting->getTaxRule()) {
            $parts[] = $rule->getName();
        }
        if (null !== $method = $accounting->getPaymentMethod()) {
            $parts[] = $method->getName();
        }
        if (null !== $tax = $accounting->getTax()) {
            $parts[] = $tax->getName();
        }
        if (null !== $groups = $accounting->getCustomerGroups()->toArray()) {
            if (empty($groups)) {
                $parts[] = 'All customer groups';
            }  else {
                $parts[] = implode('/', array_map(function (CustomerGroupInterface $group) {
                    return $group->getName();
                }, $groups));
            }
        }

        $accounting->setName(implode(' - ', $parts));
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            AccountingEvents::PRE_CREATE => ['onInsert'],
            AccountingEvents::PRE_UPDATE => ['onUpdate'],
        ];
    }
}
