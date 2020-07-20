<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Bundle\CommerceBundle\Model\NotificationTypes as BTypes;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Model\NotificationTypes as CTypes;
use Ekyna\Component\Commerce\Customer\Model\CustomerContactInterface;
use Ekyna\Component\Commerce\Customer\Model\NotificationsInterface;
use Twig\Environment;
use Twig\TemplateWrapper;

/**
 * Class CommonRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CommonRenderer
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var array
     */
    private $config;

    /**
     * @var TemplateWrapper
     */
    private $template;


    /**
     * Constructor.
     *
     * @param Environment $twig
     * @param array       $config
     */
    public function __construct(Environment $twig, array $config = [])
    {
        $this->twig   = $twig;
        $this->config = array_replace([
            'template' => '@EkynaCommerce/Show/common.html.twig',
        ], $config);
    }

    /**
     * Renders the address.
     *
     * @param AddressInterface $address
     * @param array            $options ('display_phones' and 'locale')
     *
     * @return string
     */
    public function renderAddress(AddressInterface $address, array $options = []): string
    {
        $options = array_replace([
            'display_phones' => true,
            'locale'         => null,
        ], $options);

        return $this->getTemplate()->renderBlock('address', [
            'address' => $address,
            'options' => $options,
        ]);
    }

    /**
     * Renders the customer contact.
     *
     * @param CustomerContactInterface $contact
     * @param array                    $options ('display_phones', 'locale' and 'admin')
     *
     * @return string
     */
    public function renderCustomerContact(CustomerContactInterface $contact, array $options = []): string
    {
        $options = array_replace([
            'display_phones' => true,
            'locale'         => null,
            'admin'          => false,
        ], $options);

        return $this->getTemplate()->renderBlock('customer_contact', [
            'contact' => $contact,
            'options' => $options,
        ]);
    }

    /**
     * Renders the subject's notifications.
     *
     * @param NotificationsInterface $subject
     * @param array                  $options
     *
     * @return string
     */
    public function renderNotifications(NotificationsInterface $subject, array $options = []): string
    {
        $options = array_replace([
            'inline' => false,
            'locale' => null,
        ], $options);


        return $this->getTemplate()->renderBlock('notifications', [
            'types'         => BTypes::getChoices([CTypes::MANUAL]),
            'notifications' => $subject->getNotifications(),
            'options'       => $options,
        ]);
    }

    /**
     * Returns the template.
     *
     * @return TemplateWrapper
     */
    private function getTemplate(): TemplateWrapper
    {
        if ($this->template) {
            return $this->template;
        }

        return $this->template = $this->twig->load($this->config['template']);
    }
}
