<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Bundle\CommerceBundle\Model\NotificationTypes as BTypes;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Model\NotificationTypes as CTypes;
use Ekyna\Component\Commerce\Common\Transformer\ArrayToAddressTransformer;
use Ekyna\Component\Commerce\Customer\Model\CustomerContactInterface;
use Ekyna\Component\Commerce\Customer\Model\NotificationsInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Twig\Environment;
use Twig\TemplateWrapper;

use function array_replace;
use function is_array;

/**
 * Class CommonRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CommonRenderer
{
    private Environment               $twig;
    private ArrayToAddressTransformer $addressTransformer;
    private array                     $config;

    private ?TemplateWrapper $template = null;

    public function __construct(Environment $twig, ArrayToAddressTransformer $addressTransformer, array $config = [])
    {
        $this->twig = $twig;
        $this->addressTransformer = $addressTransformer;
        $this->config = array_replace([
            'template' => '@EkynaCommerce/Show/common.html.twig',
        ], $config);
    }

    /**
     * Renders the address.
     *
     * @param array|AddressInterface $address
     * @param array                  $options ('display_phones' and 'locale')
     */
    public function renderAddress($address, array $options = []): string
    {
        if ($address instanceof AddressInterface) {
            $address = $this->addressTransformer->transformAddress($address);
        }

        if (!is_array($address)) {
            throw new UnexpectedTypeException($address, ['array', AddressInterface::class]);
        }

        $address = array_replace([
            'company'     => null,
            'first_name'  => null,
            'last_name'   => null,
            'street'      => null,
            'supplement'  => null,
            'complement'  => null,
            'extra'       => null,
            'postal_code' => null,
            'city'        => null,
            'country'     => null,
            'state'       => null,
            'phone'       => null,
            'mobile'      => null,
            'digicode1'   => null,
            'digicode2'   => null,
            'intercom'    => null,
            'information' => null,
        ], $address);

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
     * Renders the customer.
     *
     * @param array $options ('display_phones' and 'locale')
     */
    public function renderCustomer(array $customer, array $options = []): string
    {
        // TODO Accept CustomerInterface, SaleInterface and array as inputs.

        $customer = array_replace([
            'number'     => null,
            'company'    => null,
            'first_name' => null,
            'last_name'  => null,
            'email'      => null,
            'phone'      => null,
            'mobile'     => null,
        ], $customer);

        $options = array_replace([
            'display_phones' => true,
            'locale'         => null,
        ], $options);

        return $this->getTemplate()->renderBlock('customer', [
            'customer' => $customer,
            'options'  => $options,
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
